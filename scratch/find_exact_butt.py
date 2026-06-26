from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a very tight 60x60 area around the cigarette tip:
# Centered around x = 0.642 (657 px), y = 0.597 (611 px)
cx = 657
cy = 611
crop_w = 40
crop_h = 40

x1, y1 = cx - crop_w, cy - crop_h
x2, y2 = cx + crop_w, cy + crop_h

crop = img.crop((x1, y1, x2, y2))
# Resize 10x for pixel-perfect visualization
scale = 10
large = crop.resize((crop.width * scale, crop.height * scale), Image.NEAREST)
draw = ImageDraw.Draw(large)

# Draw a pixel grid and labels for each pixel in the original image coordinate space
# We will draw a crosshair for every 0.002 in relative coordinates in the region
for rx_int in range(635, 655, 2):
    rx = rx_int / 1000.0
    px = int(w * rx)
    lx = (px - x1) * scale
    if 0 <= lx < large.width:
        draw.line([(lx, 0), (lx, large.height)], fill=(100, 100, 100), width=1)
        draw.text((lx + 2, 5), f"{rx:.3f}", fill=(255, 255, 255))

for ry_int in range(585, 605, 2):
    ry = ry_int / 1000.0
    py = int(h * ry)
    ly = (py - y1) * scale
    if 0 <= ly < large.height:
        draw.line([(0, ly), (large.width, ly)], fill=(100, 100, 100), width=1)
        draw.text((5, ly + 2), f"{ry:.3f}", fill=(255, 255, 255))

# Let's also print out the RGB values of pixels around the cigarette tip to see where the red/orange ember is.
# The ember is reddish/orange, so R should be significantly higher than G and B.
print("Scanning for reddish/orange ember pixels in original image:")
for y_px in range(cy - 25, cy + 5):
    for x_px in range(cx - 25, cx + 10):
        r, g, b = img.getpixel((x_px, y_px))
        # Ember is red/orange, so R is high, G is medium, B is low
        # Let's check for pixels where R > 100 and R - G > 30 and R - B > 40
        if r > 100 and r - g > 20 and r - b > 35:
            print(f"Ember candidate at pixel x={x_px} ({x_px/w:.4f}), y={y_px} ({y_px/h:.4f}): RGB=({r},{g},{b})")

# Let's save the high-res pixel grid
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/pixel_perfect_butt.png'
large.save(dest_path)
print(f"\nSaved pixel-perfect grid to {dest_path}")
