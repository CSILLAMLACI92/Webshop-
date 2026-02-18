import urllib.parse
import urllib.request
from build_uploads_catalog import parse_products, HEADERS, BASE

def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", "ignore")

params = {"sw": "elektromos gitar", "view": "grid", "pg": 1}
url = BASE + "search.html?" + urllib.parse.urlencode(params)
print(url)
html = fetch(url)
items = parse_products(html)
print("items", len(items))
print(items[:3])
