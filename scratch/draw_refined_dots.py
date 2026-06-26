from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a 120x120 area centered at x: 64%, y: 59%
cx = int(w * 0.64)
cy = int(h * 0.59)
x1, y1 = cx - 60, cy - 60
x2, y2 = cx + 60, cy + 60

crop = img.crop((x1, y1, x2, y2))
draw = ImageDraw.Draw(crop)

# Let's draw crosses at various coordinates relative to original image:
candidates = {
    "ref_0.6475": (0.6475, 0.5918, (0, 0, 255)),   # Blue (old silhouette)
    "test_0.6377": (0.6377, 0.5918, (255, 0, 0)),  # Red
    "test_0.6350": (0.6350, 0.5900, (0, 255, 0)),  # Green
    "test_0.6320": (0.6320, 0.5890, (255, 255, 0)),# Yellow
}

for name, (rx, ry, color) in candidates.items():
    # Map to crop coordinates
    px = int(w * rx) - x1
    py = int(h * ry) - y1
    # Draw cross
    draw.line([(px - 4, py), (px + 4, py)], fill=color, width=1)
    draw.line([(px, py - 4), (px, py + 4)], fill=color, width=1)
    draw.text((px + 3, py + 3), name, fill=color)

# Save the crop
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/refined_candidates.png'
crop.save(dest_path)
print(f"Saved refined comparison crop to {dest_path}")
