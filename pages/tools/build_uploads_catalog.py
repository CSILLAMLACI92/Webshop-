import json
import os
import re
import time
import urllib.parse
import urllib.request

BASE = "https://www.thomann.de/hu/"
UPLOADS_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "uploads"))
MANIFEST_PATH = os.path.join(UPLOADS_DIR, "products_manifest.json")

HEADERS = {
    "User-Agent": "Mozilla/5.0",
    "Accept-Language": "hu-HU,hu;q=0.9,en;q=0.8",
}
FETCH_DELAY = float(os.environ.get("FETCH_DELAY", "1.5"))
RETRIES = 6
START_DELAY = float(os.environ.get("START_DELAY", "0"))

CATEGORIES = {
    "gitar": {
        "count": 50,
        "queries": [
            "electric guitar",
            "elektromos gitar",
            "stratocaster",
            "telecaster",
            "les paul",
            "ibanez rg",
            "prs se",
            "schecter",
            "esp ltd",
            "jackson dinky",
            "solar guitar",
        ],
        "exclude": [
            "case", "gigbag", "bag", "stand", "strap", "strings", "string", "cable",
            "pick", "picks", "capo", "tuner", "amp", "pedal", "pickup", "bridge",
            "nut", "jack", "switch", "volume", "tone", "cleaner", "polish", "tool",
            "adapter", "wire", "slide", "wall", "support", "mount", "hanger",
            "pickguard", "electronics", "kit"
        ],
    },
    "basszus": {
        "count": 50,
        "queries": [
            "bass guitar",
            "elektromos basszus",
            "fender jazz bass",
            "precision bass",
            "ibanez sr bass",
            "yamaha trbx",
            "music man stingray",
            "sire bass",
        ],
        "exclude": [
            "case", "gigbag", "bag", "stand", "strap", "strings", "string", "cable",
            "pick", "picks", "capo", "tuner", "amp", "pedal", "pickup", "bridge",
            "nut", "jack", "switch", "volume", "tone", "cleaner", "polish", "tool",
            "adapter", "wire", "support", "mount", "hanger", "kit"
        ],
    },
    "dob": {
        "count": 50,
        "queries": [
            "drum kit",
            "drum set",
            "dob szett",
            "acoustic drum set",
            "electronic drum kit",
            "roland td",
            "alesis drum",
            "yamaha drum set",
        ],
        "exclude": [
            "stick", "sticks", "cymbal", "snare", "head", "hardware",
            "pedal", "stand", "bag", "case", "mallet", "throne"
        ],
    },
    "billentyu": {
        "count": 50,
        "queries": [
            "keyboard",
            "digital piano",
            "stage piano",
            "synthesizer",
            "workstation keyboard",
            "midi keyboard",
        ],
        "exclude": [
            "stand", "bench", "case", "cover", "bag", "pedal", "adapter", "cable",
            "sustain pedal", "power supply"
        ],
    },
    "mikrofon": {
        "count": 50,
        "queries": [
            "microphone",
            "condenser microphone",
            "dynamic microphone",
            "wireless microphone",
            "vocal microphone",
            "podcast microphone",
        ],
        "exclude": [
            "stand", "cable", "adapter", "shockmount", "popfilter",
            "windscreen", "case", "bag", "mount", "clip"
        ],
    },
    "hangfal": {
        "count": 50,
        "queries": [
            "studio monitor",
            "active speaker",
            "pa speaker",
            "monitor speaker",
            "subwoofer",
            "bluetooth speaker",
        ],
        "exclude": [
            "stand", "mount", "case", "bag", "cable", "cover"
        ],
    },
    "tartozek": {
        "count": 50,
        "queries": [
            "guitar strings",
            "guitar strap",
            "instrument cable",
            "guitar picks",
            "capo",
            "drum sticks",
            "keyboard stand",
            "microphone stand",
            "guitar case",
        ],
        "exclude": [],
    },
}

CATEGORY_TOKENS = {
    "gitar": ["elektromos gitár", "elektromos gitárok", "electric guitar"],
    "basszus": ["elektromos basszusgitár", "elektromos basszusgitárok", "basszusgitár"],
    "dob": ["akusztikus dobok", "elektromos dobok", "dob szett", "drum set", "drum kit"],
    "billentyu": ["billentyűsök", "digitális zongorák", "szintetizátorok", "keyboard", "piano"],
    "mikrofon": ["mikrofonok", "microphone"],
    "hangfal": ["hangfal", "loudspeaker", "speaker", "monitor"],
    "tartozek": [],
}

def fetch(url: str) -> str:
    for attempt in range(RETRIES):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, timeout=30) as resp:
                return resp.read().decode("utf-8", "ignore")
        except Exception as exc:
            msg = str(exc)
            if "429" in msg or "Too Many Requests" in msg:
                time.sleep(10 + attempt * 4)
                continue
            raise
    raise RuntimeError(f"fetch failed: {url}")

def fetch_bytes(url: str) -> bytes:
    for attempt in range(RETRIES):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, timeout=30) as resp:
                return resp.read()
        except Exception as exc:
            msg = str(exc)
            if "429" in msg or "Too Many Requests" in msg:
                time.sleep(10 + attempt * 4)
                continue
            raise
    raise RuntimeError(f"fetch_bytes failed: {url}")

def search_page(query: str, page: int = 1) -> str:
    params = {"sw": query, "view": "grid", "pg": page}
    url = BASE + "search.html?" + urllib.parse.urlencode(params)
    html = fetch(url)
    time.sleep(FETCH_DELAY)
    return html


PRICE_RE = re.compile(r'itemprop="price"[^>]*content="([^"]+)"')
CURRENCY_RE = re.compile(r'itemprop="priceCurrency"[^>]*content="([^"]+)"')
OG_IMAGE_RE = re.compile(r'property="og:image"[^>]*content="([^"]+)"')

def parse_products(html: str):
    results = []
    blocks = html.split("fx-product-list-entry")
    for block in blocks[1:]:
        pid_m = re.search(r'data-product-id="(\d+)"', block)
        href_m = re.search(r'class="product__image" href="([^"]+)"', block)
        man_m = re.search(r'class="title__manufacturer">([^<]+)</span>', block)
        name_m = re.search(r'class="title__name">([^<]+)</span>', block)
        img_m = re.search(r'data-srcset="([^"]+)"', block)
        if not (pid_m and href_m and man_m and name_m and img_m):
            continue
        full_name = f"{man_m.group(1).strip()} {name_m.group(1).strip()}".strip()
        results.append({
            "id": pid_m.group(1).strip(),
            "href": href_m.group(1).strip(),
            "thumb": img_m.group(1).strip(),
            "name": full_name,
        })
    return results

def is_excluded(name: str, url: str, exclude):
    text = (name + " " + url).lower()
    return any(tok in text for tok in exclude)

def safe_filename(category: str, index: int, pid: str, url: str) -> str:
    base = f"{category}_{index:03d}_{pid}"
    ext = os.path.splitext(urllib.parse.urlparse(url).path)[1].lower()
    if ext not in [".jpg", ".jpeg", ".png", ".webp"]:
        ext = ".jpg"
    return base + ext

def collect_for_category(key: str, config: dict, seen_urls: set, target: int):
    collected = []
    for query in config["queries"]:
        page = 1
        while len(collected) < target and page <= 8:
            try:
                html = search_page(query, page)
            except Exception:
                break
            items = parse_products(html)
            if not items:
                break
            for item in items:
                if len(collected) >= target:
                    break
                href = item["href"]
                product_url = urllib.parse.urljoin(BASE, href)
                if product_url in seen_urls:
                    continue
                if is_excluded(item["name"], href, config["exclude"]):
                    continue
                try:
                    p_html = fetch(product_url)
                    time.sleep(FETCH_DELAY)
                except Exception:
                    continue
                tokens = CATEGORY_TOKENS.get(key, [])
                if tokens:
                    low = p_html.lower()
                    if not any(t in low for t in tokens):
                        continue

                price_m = PRICE_RE.search(p_html)
                currency_m = CURRENCY_RE.search(p_html)
                og_m = OG_IMAGE_RE.search(p_html)
                if not price_m or not og_m:
                    continue
                price = price_m.group(1)
                currency = currency_m.group(1) if currency_m else "HUF"
                if currency.upper() != "HUF":
                    continue
                image_url = og_m.group(1)
                collected.append({
                    "category": key,
                    "name": item["name"],
                    "price_huf": float(price),
                    "source_url": product_url,
                    "image_url": image_url,
                    "pid": item["id"],
                })
                seen_urls.add(product_url)
            page += 1
            time.sleep(FETCH_DELAY)
    return collected

def load_manifest():
    if not os.path.exists(MANIFEST_PATH):
        return []
    with open(MANIFEST_PATH, "r", encoding="utf-8") as f:
        try:
            return json.load(f)
        except Exception:
            return []

def save_manifest(manifest):
    with open(MANIFEST_PATH, "w", encoding="utf-8") as f:
        json.dump(manifest, f, ensure_ascii=False, indent=2)

def main():
    os.makedirs(UPLOADS_DIR, exist_ok=True)
    manifest = load_manifest()
    seen_urls = {m.get("source_url") for m in manifest if m.get("source_url")}
    if START_DELAY > 0:
        time.sleep(START_DELAY)

    import sys
    only_category = None
    if len(sys.argv) > 1:
        only_category = sys.argv[1].strip().lower()

    import sys
    max_items = int(os.environ.get("MAX_ITEMS", "0") or "0")
    for key, cfg in CATEGORIES.items():
        if only_category and key != only_category:
            continue
        existing = [m for m in manifest if m.get("category") == key]
        need = max(0, cfg["count"] - len(existing))
        if need == 0:
            print(f"Skip {key}: already {len(existing)}")
            continue
        print(f"Collecting {key} ({need} needed)...")
        if max_items > 0:
            need = min(need, max_items)
        items = collect_for_category(key, cfg, seen_urls, need)
        if len(items) < need:
            print(f"Warning: only {len(items)} items for {key}")
        start_idx = len(existing) + 1
        for offset, item in enumerate(items):
            idx = start_idx + offset
            filename = safe_filename(key, idx, item["pid"], item["image_url"])
            local_path = os.path.join(UPLOADS_DIR, filename)
            if not os.path.exists(local_path):
                try:
                    data = fetch_bytes(item["image_url"])
                    with open(local_path, "wb") as f:
                        f.write(data)
                    time.sleep(FETCH_DELAY)
                except Exception:
                    continue
            item["image_path"] = f"/uploads/{filename}"
            manifest.append(item)
            save_manifest(manifest)
    with open(MANIFEST_PATH, "w", encoding="utf-8") as f:
        json.dump(manifest, f, ensure_ascii=False, indent=2)
    print(f"Saved {len(manifest)} items to {MANIFEST_PATH}")

if __name__ == "__main__":
    main()
