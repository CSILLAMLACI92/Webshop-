try:
    from PIL import Image
    print("PIL ok")
except Exception as e:
    print("PIL missing", e)
