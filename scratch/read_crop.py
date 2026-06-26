from PIL import Image

# Load the user's cropped cigarette tip image to see its colors and size
img = Image.open('C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782488384063.jpg')
w, h = img.size
print(f"Crop image dimensions: {w}x{h}")

# Let's print the colors of some pixels around the middle to see what they look like
for y in range(h):
    row_pixels = []
    for x in range(w):
        r, g, b = img.getpixel((x, y))[:3]
        row_pixels.append(f"({r},{g},{b})")
    # print a few rows
    if y % 5 == 0 or y == h//2:
        print(f"Row {y} sample pixels: {', '.join(row_pixels[:10])}...")
