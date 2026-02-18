import json
from collections import Counter

with open(r"..\uploads\products_manifest.json", "r", encoding="utf-8") as f:
    data = json.load(f)

c = Counter([d.get("category") for d in data])
print(c)
print("total", len(data))
print("sample", data[:2])
