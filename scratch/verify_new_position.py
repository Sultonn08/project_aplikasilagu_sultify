from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# New coordinates: x=0.580, y=0.590
CIG_REL_X = 0.580
CIG_REL_Y = 0.590

cx = int(w * CIG_REL_X)
cy = int(h * CIG_REL_Y)

# Crop around the point
crop_size = 100
x1, y1 = max(0, cx - crop_size), max(0, cy - crop_size)
x2, y2 = min(w, cx + crop_size), min(h, cy + crop_size)

crop = img.crop((x1, y1, x2, y2))
draw = ImageDraw.Draw(crop)

# Draw a bright red crosshair at the exact target point
px = cx - x1
py = cy - y1
draw.line([(px - 10, py), (px + 10, py)], fill=(255, 0, 0), width=2)
draw.line([(px, py - 10), (px, py + 10)], fill=(255, 0, 0), width=2)
draw.ellipse([(px-4, py-4), (px+4, py+4)], fill=(255, 165, 0))
draw.text((px + 12, py - 5), f"SMOKE HERE ({CIG_REL_X},{CIG_REL_Y})", fill=(255, 255, 0))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/new_cig_position.png'
crop.save(dest_path)
print(f"Saved to {dest_path}")
print(f"Target in pixels: x={cx}, y={cy}")
print(f"Target relative: X={CIG_REL_X}, Y={CIG_REL_Y}")
