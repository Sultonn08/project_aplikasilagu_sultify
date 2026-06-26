from PIL import Image

img_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782490376579.jpg'

img = Image.open(img_path).convert('RGB')
w, h = img.size

# Print pixels in the upper half where R-B is largest (to find orange/red pixels)
peaks = []
for y in range(h - 30): # avoid the white text at the bottom
    for x in range(w):
        r, g, b = img.getpixel((x, y))
        # Orange/red has high R compared to B, and G is moderate
        diff = r - b
        if r > 80 and diff > 40:
            peaks.append((x, y, r, g, b, diff))

# Sort by diff descending
peaks.sort(key=lambda x: x[5], reverse=True)

print("Top 15 most orange/red pixels:")
for p in peaks[:15]:
    print(f"x={p[0]}, y={p[1]}, RGB=({p[2]},{p[3]},{p[4]}), diff={p[5]}")
