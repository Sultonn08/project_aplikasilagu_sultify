from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# From the first grid view, the man's silhouette is clearly visible
# and his raised hand/arm with cigarette extends forward
# The cigarette tip appears to be around x=55-57%, y=53-56%
# Let's create a MUCH wider view showing the man's upper body

cx = int(w * 0.60)
cy = int(h * 0.55)
crop_size = 200  # larger area

x1, y1 = max(0, cx - crop_size), max(0, cy - crop_size) 
x2, y2 = min(w, cx + crop_size), min(h, cy + crop_size)

crop = img.crop((x1, y1, x2, y2))
cw, ch = crop.size

draw = ImageDraw.Draw(crop)

# Draw precise grid every 2% (easier to read)
for x_pct in range(35, 82, 2):
    cx_pct = int(w * x_pct / 100) - x1
    if 0 <= cx_pct < cw:
        color = (255, 0, 0) if x_pct % 10 == 0 else (120, 0, 0)
        draw.line([(cx_pct, 0), (cx_pct, ch)], fill=color, width=1)
        if x_pct % 4 == 0:
            draw.text((cx_pct + 1, 3), f"{x_pct}", fill=(255, 100, 100))

for y_pct in range(33, 78, 2):
    cy_pct = int(h * y_pct / 100) - y1
    if 0 <= cy_pct < ch:
        color = (0, 255, 0) if y_pct % 10 == 0 else (0, 120, 0)
        draw.line([(0, cy_pct), (cw, cy_pct)], fill=color, width=1)
        if y_pct % 4 == 0:
            draw.text((3, cy_pct + 1), f"{y_pct}", fill=(100, 255, 100))

# Mark old current
cm_old_x = int(w * 0.635) - x1
cm_old_y = int(h * 0.590) - y1
draw.ellipse([(cm_old_x-5, cm_old_y-5), (cm_old_x+5, cm_old_y+5)], outline=(255,255,0), width=2)
draw.text((cm_old_x + 7, cm_old_y), "OLD pos", fill=(255, 255, 0))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/person_wide_grid.png'
crop.save(dest_path)
print(f"Saved to {dest_path}")
