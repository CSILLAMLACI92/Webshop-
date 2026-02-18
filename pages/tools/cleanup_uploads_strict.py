import os

UPLOADS_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "uploads"))

KEEP_PREFIXES = ("pfp_",)
KEEP_FILES = {"Default.avatar.jpg", "__write_test.txt"}

removed = 0
for name in os.listdir(UPLOADS_DIR):
    if name in KEEP_FILES:
        continue
    if name.startswith(KEEP_PREFIXES):
        continue
    try:
        os.remove(os.path.join(UPLOADS_DIR, name))
        removed += 1
    except OSError:
        pass

print("removed", removed)
