import re
import urllib.parse
import urllib.request

BASE = "https://www.thomann.de/hu/"
HEADERS = {"User-Agent": "Mozilla/5.0", "Accept-Language": "hu-HU,hu;q=0.9,en;q=0.8"}

def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", "ignore")

def search_page(query: str):
    params = {"sw": query, "view": "grid", "pg": 1}
    url = BASE + "search.html?" + urllib.parse.urlencode(params)
    print("fetch", url)
    return fetch(url)

def parse_products(html: str):
    results = []
    blocks = html.split("fx-product-list-entry")
    for block in blocks[1:]:
        pid_m = re.search(r'data-product-id="(\d+)"', block)
        href_m = re.search(r'class="product__image" href="([^"]+)"', block)
        man_m = re.search(r'class="title__manufacturer">([^<]+)</span>', block)
        name_m = re.search(r'class="title__name">([^<]+)</span>', block)
        if not (pid_m and href_m and man_m and name_m):
            continue
        full_name = f"{man_m.group(1).strip()} {name_m.group(1).strip()}".strip()
        results.append({
            "id": pid_m.group(1).strip(),
            "href": href_m.group(1).strip(),
            "name": full_name,
        })
    return results

PRICE_RE = re.compile(r'itemprop="price"[^>]*content="([^"]+)"')
CURRENCY_RE = re.compile(r'itemprop="priceCurrency"[^>]*content="([^"]+)"')
OG_IMAGE_RE = re.compile(r'property="og:image"[^>]*content="([^"]+)"')

html = search_page("ibanez rg421")
items = parse_products(html)
print("items", len(items))
for item in items[:3]:
    url = urllib.parse.urljoin(BASE, item["href"])
    print("product", item["name"], url)
    p_html = fetch(url)
    price = PRICE_RE.search(p_html)
    currency = CURRENCY_RE.search(p_html)
    og = OG_IMAGE_RE.search(p_html)
    print("price", price.group(1) if price else None, "cur", currency.group(1) if currency else None)
    print("img", og.group(1) if og else None)
