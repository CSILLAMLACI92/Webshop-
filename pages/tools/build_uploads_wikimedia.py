import json
import os
import re
import time
import urllib.parse
import urllib.request

API = "https://commons.wikimedia.org/w/api.php"
UPLOADS_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "uploads"))
MANIFEST_PATH = os.path.join(UPLOADS_DIR, "products_manifest_wikimedia.json")

HEADERS = {"User-Agent": "Mozilla/5.0"}
SLEEP = 0.1

CATEGORIES = {
    "gitar": {
        "category": "Category:Electric_guitars",
        "search": ["electric guitar", "guitar", "solid body guitar", "stratocaster", "telecaster", "les paul"],
    },
    "basszus": {
        "category": "Category:Bass_guitars",
        "search": ["bass guitar", "electric bass", "jazz bass", "precision bass"],
    },
    "dob": {
        "category": "Category:Drum-kits",
        "search": ["drum kit", "drum set", "drums", "electronic drum", "acoustic drum"],
    },
    "billentyu": {
        "category": "Category:Electronic_keyboards",
        "search": ["keyboard", "electronic keyboard", "digital piano", "synthesizer", "stage piano"],
    },
    "mikrofon": {
        "category": "Category:Microphones",
        "search": ["microphone", "condenser microphone", "dynamic microphone", "studio microphone"],
    },
    "hangfal": {
        "category": "Category:Loudspeakers",
        "search": ["loudspeaker", "speaker", "studio monitor", "PA speaker"],
    },
    "tartozek": {
        "category": "Category:Musical_instrument_parts_and_accessories",
        "search": ["guitar strings", "guitar strap", "instrument cable", "guitar picks", "capo", "drum sticks", "microphone stand"],
    },
}

BAD_NAME_TOKENS = [
    "logo", "poster", "sheet music", "score", "diagram",
    "drawing", "sketch", "icon", "infographic",
]

def api_get(params):
    params = dict(params)
    params["format"] = "json"
    url = API + "?" + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode("utf-8", "ignore"))

def fetch_bytes(url):
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read()

def get_category_files(category_title, limit=2000):
    files = []
    cont = None
    while len(files) < limit:
        params = {
            "action": "query",
            "list": "categorymembers",
            "cmtitle": category_title,
            "cmtype": "file",
            "cmlimit": 500,
        }
        if cont:
            params["cmcontinue"] = cont
        data = api_get(params)
        members = data.get("query", {}).get("categorymembers", [])
        for m in members:
            title = m.get("title", "")
            if title.startswith("File:"):
                files.append(title)
        cont = data.get("continue", {}).get("cmcontinue")
        if not cont:
            break
    return files

def search_files(term, limit=500):
    results = []
    cont = None
    while len(results) < limit:
        params = {
            "action": "query",
            "list": "search",
            "srnamespace": 6,
            "srlimit": 50,
            "srsearch": term,
        }
        if cont:
            params["sroffset"] = cont
        data = api_get(params)
        items = data.get("query", {}).get("search", [])
        for it in items:
            title = it.get("title", "")
            if title.startswith("File:"):
                results.append(title)
        cont = data.get("continue", {}).get("sroffset")
        if cont is None:
            break
    return results

def get_image_urls(titles):
    urls = []
    for i in range(0, len(titles), 50):
        batch = titles[i:i+50]
        data = api_get({
            "action": "query",
            "prop": "imageinfo",
            "iiprop": "url",
            "titles": "|".join(batch),
        })
        pages = data.get("query", {}).get("pages", {})
        for page in pages.values():
            title = page.get("title", "")
            info = page.get("imageinfo", [])
            if not info:
                continue
            url = info[0].get("url")
            if url:
                urls.append((title, url))
    return urls

def clean_name(title):
    name = title.replace("File:", "")
    name = re.sub(r"\\.[A-Za-z0-9]+$", "", name)
    name = name.replace("_", " ").strip()
    return name

def is_bad_name(name):
    low = name.lower()
    return any(tok in low for tok in BAD_NAME_TOKENS)

def safe_filename(category, index, url):
    ext = os.path.splitext(urllib.parse.urlparse(url).path)[1].lower()
    if ext not in [".jpg", ".jpeg", ".png", ".webp"]:
        ext = ".jpg"
    return f"{category}_{index:03d}{ext}"

def main():
    os.makedirs(UPLOADS_DIR, exist_ok=True)
    manifest = []
    used_urls = set()
    for category, cfg in CATEGORIES.items():
        print("Collecting", category)
        titles = []
        if cfg.get("category"):
            titles.extend(get_category_files(cfg["category"]))
        for term in cfg.get("search", []):
            titles.extend(search_files(term))
        # de-dup titles
        seen_titles = set()
        dedup = []
        for t in titles:
            if t in seen_titles:
                continue
            seen_titles.add(t)
            dedup.append(t)
        titles = dedup

        titles = [t for t in titles if not is_bad_name(clean_name(t))]
        image_pairs = get_image_urls(titles)

        count = 0
        index = 1
        for title, url in image_pairs:
            if count >= 50:
                break
            if url in used_urls:
                continue
            name = clean_name(title)
            if is_bad_name(name):
                continue
            filename = safe_filename(category, index, url)
            dest = os.path.join(UPLOADS_DIR, filename)
            try:
                data = fetch_bytes(url)
            except Exception:
                continue
            with open(dest, "wb") as f:
                f.write(data)
            manifest.append({
                "category": category,
                "name": name,
                "image_url": url,
                "image_path": f"/uploads/{filename}",
                "source": "Wikimedia Commons",
            })
            used_urls.add(url)
            count += 1
            index += 1
            time.sleep(SLEEP)

        print(category, "saved", count)

    with open(MANIFEST_PATH, "w", encoding="utf-8") as f:
        json.dump(manifest, f, ensure_ascii=False, indent=2)
    print("Saved manifest", MANIFEST_PATH, "items", len(manifest))

if __name__ == "__main__":
    main()
