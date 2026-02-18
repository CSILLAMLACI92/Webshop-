import re
import sys
import urllib.parse
import urllib.request
from build_uploads_catalog import parse_products

URL = sys.argv[1] if len(sys.argv) > 1 else "https://www.thomann.de/hu/search.html?sw=elektromos+gitar&view=grid"

def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", "ignore")

html = fetch(URL)
print("len", len(html))
print("fx-product-list-entry count", html.count("fx-product-list-entry"))
items = parse_products(html)
print("parsed items", len(items))
print("parsed sample", items[:3])
blocks = html.split("fx-product-list-entry")
if len(blocks) > 1:
    print("block sample", blocks[1][:500].replace("\n", " "))
    b = blocks[1]
    import re as _re
    print("pid raw", _re.search(r'data-product-id', b))
    print("pid", _re.search(r'data-product-id=\"(\\d+)\"', b))
    print("pid2", _re.search(r'data-product-id=\"([0-9]+)\"', b))
    print("pid3", _re.search(r'data-product-id=\"([0-9]+)\"', b).group(1) if _re.search(r'data-product-id=\"([0-9]+)\"', b) else None)
    print("href", _re.search(r'class=\"product__image\" href=\"([^\"]+)\"', b))
    print("man", _re.search(r'class=\"title__manufacturer\">([^<]+)</span>', b))
    print("name", _re.search(r'class=\"title__name\">([^<]+)</span>', b))
    pos = b.find("data-product-id")
    if pos != -1:
        print("pid snippet repr", repr(b[pos-10:pos+40]))

# Find candidate product links
link_re = re.compile(r'href="([^"]+)"')
links = [l for l in link_re.findall(html) if ".htm" in l]
print("links", len(links))
print("sample links", links[:10])

# Find product-like containers by class tokens
class_re = re.compile(r'class="([^"]+)"')
classes = class_re.findall(html)
tokens = {}
for cls in classes:
    for t in cls.split():
        tokens[t] = tokens.get(t, 0) + 1

interesting = sorted([(k, v) for k, v in tokens.items() if "product" in k], key=lambda x: -x[1])
print("product tokens", interesting[:20])

# Show snippet around first product token
idx = html.find("product__")
if idx != -1:
    print("snippet", html[idx-200:idx+400].replace("\n", " "))

# Inspect first product page for JSON-LD price/image
base = "https://www.thomann.de/hu/"
product_links = []
for m in re.finditer(r'<a class="product__image" href="([^"]+)"', html):
    product_links.append(m.group(1))

if product_links:
    from urllib.parse import urljoin
    product_url = urljoin(base, product_links[0])
    print("product url", product_url)
    p_html = fetch(product_url)
    scripts = re.findall(r'<script type="application/ld\\+json">([\\s\\S]*?)</script>', p_html)
    print("ldjson count", len(scripts))
    if scripts:
        for s in scripts[:2]:
            print("jsonld snippet", s[:400].replace("\n", " "))
    m2 = re.search(r'itemprop="price"[^>]*content="([^"]+)"', p_html)
    print("itemprop price", m2.group(1) if m2 else None)
    m3 = re.search(r'itemprop="priceCurrency"[^>]*content="([^"]+)"', p_html)
    print("price currency", m3.group(1) if m3 else None)
    m4 = re.search(r'property="og:image"[^>]*content="([^"]+)"', p_html)
    print("og:image", m4.group(1) if m4 else None)
    m5 = re.search(r'itemprop="image"[^>]*content="([^"]+)"', p_html)
    print("itemprop image", m5.group(1) if m5 else None)
    if "breadcrumb" in p_html:
        bc = re.findall(r'breadcrumb[^>]*>\\s*<a[^>]*>([^<]+)</a>', p_html)
        if bc:
            print("breadcrumbs", bc[:10])
