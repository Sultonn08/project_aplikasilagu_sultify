from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# From the grid view, the man's hand and cigarette tip appear to be around:
# x: 55-58%, y: 53-57%
# Let's mark a few candidates to find the best match

cx = int(w * 0.575)
cy = int(h * 0.540)
crop_size = 80

x1, y1 = cx - crop_size, cy - crop_size
x2, y2 = cx + crop_size, cy + crop_size

crop = img.crop((x1, y1, x2, y2))
cw, ch = crop.size

draw = ImageDraw.Draw(crop)

# Draw grid every 1%
for x_pct in range(50, 68):
    cx_pct = int(w * x_pct / 100) - x1
    if 0 <= cx_pct < cw:
        color = (200, 0, 0) if x_pct % 5 == 0 else (80, 0, 0)
        draw.line([(cx_pct, 0), (cx_pct, ch)], fill=color, width=1)
        if x_pct % 2 == 0:
            draw.text((cx_pct + 1, 3), f"{x_pct}%", fill=(255, 100, 100))

for y_pct in range(47, 63):
    cy_pct = int(h * y_pct / 100) - y1
    if 0 <= cy_pct < ch:
        color = (0, 200, 0) if y_pct % 5 == 0 else (0, 80, 0)
        draw.line([(0, cy_pct), (cw, cy_pct)], fill=color, width=1)
        if y_pct % 2 == 0:
            draw.text((3, cy_pct + 1), f"{y_pct}%", fill=(100, 255, 100))

# Mark old current
cm_old_x = int(w * 0.635) - x1
cm_old_y = int(h * 0.590) - y1
if 0 <= cm_old_x < cw and 0 <= cm_old_y < ch:
    draw.text((cm_old_x, cm_old_y), "X OLD", fill=(255, 255, 255))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/cigarette_tip_fine.png'
crop.save(dest_path)
print(f"Saved to {dest_path}")
