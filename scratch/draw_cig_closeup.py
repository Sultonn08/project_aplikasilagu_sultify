from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# From the wide grid view, the cigarette appears to extend from the man's hand
# towards the LEFT. The tip appears to be at approximately:
# x: ~47-49% (from left), y: ~52-54% (from top)
# BUT looking at the image, the man holds the cigarette UP near his mouth
# The tip would be near the LEFT end of the cigarette stick

# Let's create a crop focused on the cigarette area
cx = int(w * 0.52)  # Center slightly left of mid
cy = int(h * 0.54)
crop_size = 100

x1, y1 = max(0, cx - crop_size), max(0, cy - crop_size)
x2, y2 = min(w, cx + crop_size), min(h, cy + crop_size)

crop = img.crop((x1, y1, x2, y2))
cw, ch = crop.size

draw = ImageDraw.Draw(crop)

# Draw grid every 1% of original image width
for x_pct in range(37, 67, 1):
    cx_pct = int(w * x_pct / 100) - x1
    if 0 <= cx_pct < cw:
        color = (200, 50, 50) if x_pct % 5 == 0 else (80, 20, 20)
        draw.line([(cx_pct, 0), (cx_pct, ch)], fill=color, width=1)
        if x_pct % 2 == 0:
            draw.text((cx_pct + 1, 3), f"{x_pct}", fill=(255, 100, 100))

for y_pct in range(42, 67, 1):
    cy_pct = int(h * y_pct / 100) - y1
    if 0 <= cy_pct < ch:
        color = (50, 200, 50) if y_pct % 5 == 0 else (20, 80, 20)
        draw.line([(0, cy_pct), (cw, cy_pct)], fill=color, width=1)
        if y_pct % 2 == 0:
            draw.text((3, cy_pct + 1), f"{y_pct}", fill=(100, 255, 100))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/cig_tip_closeup.png'
crop.save(dest_path)
print(f"Saved to {dest_path}")
