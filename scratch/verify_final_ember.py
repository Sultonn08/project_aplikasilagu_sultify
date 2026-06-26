from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

CIG_REL_X = 0.695
CIG_REL_Y = 0.665

# Crop around the new position
cx = int(w * CIG_REL_X)
cy = int(h * CIG_REL_Y)
crop_size = 80

x1, y1 = max(0, cx - crop_size), max(0, cy - crop_size)
x2, y2 = min(w, cx + crop_size), min(h, cy + crop_size)

crop = img.crop((x1, y1, x2, y2))
# Scale up 3x
c1 = crop.resize((crop.width * 3, crop.height * 3), Image.LANCZOS)

draw = ImageDraw.Draw(c1)
px = (cx - x1) * 3
py = (cy - y1) * 3
draw.line([(px-10, py), (px+10, py)], fill=(255, 0, 0), width=2)
draw.line([(px, py-10), (px, py+10)], fill=(255, 0, 0), width=2)
draw.ellipse([(px-5, py-5), (px+5, py+5)], fill=(255, 165, 0))
draw.text((px+8, py-8), f"EMBER ({CIG_REL_X},{CIG_REL_Y})", fill=(255, 255, 0))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/final_ember_position.png'
c1.save(dest_path)
print(f"Saved to {dest_path}")
