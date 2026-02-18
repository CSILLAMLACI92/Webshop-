import re
import urllib.parse
import urllib.request

url = "https://www.thomann.de/hu/search.html"
params = {"sw": "ibanez rg421", "view": "grid"}
full = url + "?" + urllib.parse.urlencode(params)
req = urllib.request.Request(full, headers={"User-Agent": "Mozilla/5.0"})
html = urllib.request.urlopen(req, timeout=30).read().decode("utf-8", "ignore")
print("itemprop price in search", 'itemprop="price"' in html)
m = re.search(r'itemprop="price"[^>]*content="([^"]+)"', html)
print("sample price", m.group(1) if m else None)
print("data-price token", "data-price" in html)
