from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# We know the cigarette tip is around x = 60.5% to 62.0%, y = 57.5% to 59.0%
# Let's crop this region and save it with a pixel-level grid and labels
x1 = int(w * 0.59)
y1 = int(h * 0.56)
x2 = int(w * 0.65)
y2 = int(h * 0.62)

crop = img.crop((x1, y1, x2, y2))
# Resize 10x for extremely clear visualization of pixels
scale = 10
large = crop.resize((crop.width * scale, crop.height * scale), Image.NEAREST)
draw = ImageDraw.Draw(large)

# Draw a grid and pixel coordinate labels
for x_val in range(x1, x2):
    lx = (x_val - x1) * scale
    draw.line([(lx, 0), (lx, large.height)], fill=(50, 50, 50), width=1)
    # Add label every 5 pixels
    if x_val % 5 == 0:
        draw.text((lx + 2, 2), f"{x_val}\n({x_val/w:.3f})", fill=(255, 255, 255))

for y_val in range(y1, y2):
    ly = (y_val - y1) * scale
    draw.line([(0, ly), (large.width, ly)], fill=(50, 50, 50), width=1)
    # Add label every 5 pixels
    if y_val % 5 == 0:
        draw.text((2, ly + 2), f"{y_val}\n({y_val/h:.3f})", fill=(255, 255, 255))

# Let's also print out the RGB and brightness of pixels in this region to find the white tip.
# The white tip of the cigarette should be significantly brighter than the dark background water/silhouette.
print("Scanning for the cigarette tip pixels:")
for y_val in range(int(h * 0.57), int(h * 0.60)):
    row_str = f"y={y_val} ({y_val/h:.3f}): "
    for x_val in range(int(w * 0.60), int(w * 0.63)):
        r, g, b = img.getpixel((x_val, y_val))
        brightness = (r + g + b) // 3
        # print bright pixels
        if brightness > 90:
            row_str += f"x={x_val}({brightness}) "
    if "x=" in row_str:
        print(row_str)

# Save
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/pixel_grid.png'
large.save(dest_path)
print(f"Saved pixel grid to {dest_path}")
