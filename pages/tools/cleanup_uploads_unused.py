import json
import os

UPLOADS_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "uploads"))
MANIFEST_PATH = os.path.join(UPLOADS_DIR, "products_manifest.json")

with open(MANIFEST_PATH, "r", encoding="utf-8") as f:
    data = json.load(f)

used = set()
for item in data:
    path = item.get("image_path") or ""
    if path.startswith("/uploads/"):
        used.add(os.path.basename(path))

removed = 0
for name in os.listdir(UPLOADS_DIR):
    if not name.lower().startswith("gitar_"):
        continue
    if name not in used:
        try:
            os.remove(os.path.join(UPLOADS_DIR, name))
            removed += 1
        except OSError:
            pass

print("removed", removed)
