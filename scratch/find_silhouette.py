from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# The man is a dark silhouette. Let's find the bounding box of very dark pixels (R < 35, G < 35, B < 35)
# in the bottom right quadrant (x > 0.5 * w, y > 0.4 * h)
dark_pixels = []
for y in range(int(h * 0.4), h):
    for x in range(int(w * 0.5), w):
        r, g, b = img.getpixel((x, y))
        # Silhouette condition
        if r < 30 and g < 30 and b < 30:
            dark_pixels.append((x, y))

if dark_pixels:
    xs = [p[0] for p in dark_pixels]
    ys = [p[1] for p in dark_pixels]
    min_x, max_x = min(xs), max(xs)
    min_y, max_y = min(ys), max(ys)
    print(f"Man's silhouette region: X [{min_x} - {max_x}] ({min_x/w:.3f} - {max_x/w:.3f}), Y [{min_y} - {max_y}] ({min_y/h:.3f} - {max_y/h:.3f})")
else:
    print("No dark silhouette found")

# Let's save a low-resolution representation of the silhouette to understand its shape
# We can print an ASCII map of the bottom-right region (x: 0.5 to 1.0, y: 0.4 to 1.0)
scale_x = 40
scale_y = 30
print("ASCII Silhouette Map:")
for sy in range(scale_y):
    y = int(h * 0.4 + sy * (h * 0.6 / scale_y))
    row = []
    for sx in range(scale_x):
        x = int(w * 0.5 + sx * (w * 0.5 / scale_x))
        r, g, b = img.getpixel((x, y))
        # if very dark, print '#' otherwise '.'
        if r < 30 and g < 30 and b < 30:
            row.append('#')
        elif r > 100 and g > 100: # bright background/fog/lake
            row.append(' ')
        else:
            row.append('.')
    print("".join(row))
