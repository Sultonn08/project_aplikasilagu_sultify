from PIL import Image, ImageDraw

# Load the background image
img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop from x: 55% to 75%, y: 50% to 70%
pct_x1, pct_x2 = 55, 75
pct_y1, pct_y2 = 50, 70

x1 = int(w * pct_x1 / 100)
x2 = int(w * pct_x2 / 100)
y1 = int(h * pct_y1 / 100)
y2 = int(h * pct_y2 / 100)

crop = img.crop((x1, y1, x2, y2))
crop_w, crop_h = crop.size

# Draw grid on the cropped image
draw = ImageDraw.Draw(crop)

# We want grid lines every 1% of the original image
for pct_x in range(pct_x1, pct_x2 + 1):
    # Calculate x in crop coordinates
    cx = int(w * pct_x / 100) - x1
    color = (255, 0, 0) if pct_x % 5 == 0 else (100, 100, 100)
    width = 2 if pct_x % 5 == 0 else 1
    draw.line([(cx, 0), (cx, crop_h)], fill=color, width=width)
    if pct_x % 2 == 0:
        draw.text((cx + 2, 5), f"{pct_x}%", fill=(255, 255, 0))

for pct_y in range(pct_y1, pct_y2 + 1):
    # Calculate y in crop coordinates
    cy = int(h * pct_y / 100) - y1
    color = (255, 0, 0) if pct_y % 5 == 0 else (100, 100, 100)
    width = 2 if pct_y % 5 == 0 else 1
    draw.line([(0, cy), (crop_w, cy)], fill=color, width=width)
    if pct_y % 2 == 0:
        draw.text((5, cy + 2), f"{pct_y}%", fill=(255, 255, 0))

# Save the detailed crop to the artifacts folder so we can reference it
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/lake_guy_detailed_grid.png'
crop.save(dest_path)
print(f"Saved detailed grid image to {dest_path}")
