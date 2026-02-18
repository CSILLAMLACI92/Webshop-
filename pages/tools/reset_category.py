import json
import os
import sys

UPLOADS_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "uploads"))
MANIFEST_PATH = os.path.join(UPLOADS_DIR, "products_manifest.json")

if len(sys.argv) < 2:
    raise SystemExit("usage: reset_category.py <category>")

category = sys.argv[1].strip().lower()

with open(MANIFEST_PATH, "r", encoding="utf-8") as f:
    data = json.load(f)

kept = []
removed = []
for item in data:
    if item.get("category") == category:
        removed.append(item)
    else:
        kept.append(item)

for item in removed:
    path = item.get("image_path") or ""
    if path.startswith("/uploads/"):
        fname = os.path.basename(path)
        fpath = os.path.join(UPLOADS_DIR, fname)
        if os.path.exists(fpath):
            try:
                os.remove(fpath)
            except OSError:
                pass

with open(MANIFEST_PATH, "w", encoding="utf-8") as f:
    json.dump(kept, f, ensure_ascii=False, indent=2)

print("removed", len(removed))
