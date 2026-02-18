import re
import urllib.request

url = "https://www.thomann.de/hu/ibanez_rg421ex_bkf.htm?type=quickSearch"
req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
html = urllib.request.urlopen(req, timeout=30).read().decode("utf-8", "ignore")

tokens = ["E-Gitarren", "Elektromos", "Electric Guitars", "Guitar", "Gitár"]
for t in tokens:
    if t.lower() in html.lower():
        print("found", t)

for pat in [r'category', r'Kategorie', r'Kat', r'breadcrumb']:
    if pat.lower() in html.lower():
        print("contains", pat)

m = re.search(r'breadcrumb[^>]*>\\s*<a[^>]*>([^<]+)</a>', html)
print("breadcrumb sample", m.group(1) if m else None)
