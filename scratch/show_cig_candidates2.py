from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Test a range of coordinates to find the EXACT tip
candidates = [
    (0.580, 0.590, "current"),
    (0.597, 0.583, "candidate1"),
    (0.605, 0.578, "candidate2"),  
    (0.610, 0.575, "candidate3"),
    (0.590, 0.585, "candidate4"),
]

cx_base = int(w * 0.595)
cy_base = int(h * 0.582)
crop_size = 80

x1, y1 = max(0, cx_base - crop_size), max(0, cy_base - crop_size)
x2, y2 = min(w, cx_base + crop_size), min(h, cy_base + crop_size)

crop = img.crop((x1, y1, x2, y2))
draw = ImageDraw.Draw(crop)

colors = [(255, 0, 0), (0, 255, 0), (0, 0, 255), (255, 255, 0), (255, 0, 255)]

for i, (rx, ry, name) in enumerate(candidates):
    px = int(w * rx) - x1
    py = int(h * ry) - y1
    if 0 <= px < crop.width and 0 <= py < crop.height:
        draw.line([(px - 6, py), (px + 6, py)], fill=colors[i], width=1)
        draw.line([(px, py - 6), (px, py + 6)], fill=colors[i], width=1)
        draw.text((px + 5, py - 10), f"{name}({rx},{ry})", fill=colors[i])

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/cig_tip_candidates2.png'
crop.save(dest_path)
print(f"Saved to {dest_path}")
