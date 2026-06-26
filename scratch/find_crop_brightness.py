from PIL import Image

# Read the user's crop
img = Image.open('C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782488384063.jpg').convert('RGB')
w, h = img.size

# Let's find the brightest pixels in the crop
pixels = []
for y in range(h):
    for x in range(w):
        r, g, b = img.getpixel((x, y))
        brightness = (r + g + b) / 3.0
        # If there's an ember, it might be brighter or have some specific color.
        # Let's also compute distance to an orange-ish color (e.g. 180, 100, 50)
        pixels.append((x, y, r, g, b, brightness))

# Sort by brightness
pixels.sort(key=lambda p: p[5], reverse=True)
print("Top 10 brightest pixels in user's crop:")
for i in range(10):
    x, y, r, g, b, br = pixels[i]
    print(f"  {i}: x={x}, y={y}, rgb=({r},{g},{b}), brightness={br:.1f}")

# Let's check the bottom-left or top-right or where the tip of the cigarette is.
# In the prompt image 2, the cigarette is pointing to the left. The tip is on the left side of the crop.
# Let's print out pixels on the left side (x < 30) of the crop.
print("\nLeft side pixels of user's crop (x < 25, y around middle):")
for y in range(h // 4, 3 * h // 4, 5):
    row_pixels = []
    for x in range(0, 25, 3):
        r, g, b = img.getpixel((x, y))
        row_pixels.append(f"{x},{y}:({r},{g},{b})")
    print(f"  y={y}: {'; '.join(row_pixels)}")
