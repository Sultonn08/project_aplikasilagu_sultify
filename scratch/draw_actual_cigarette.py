from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a 200x200 area centered at x: 68%, y: 60%
cx = int(w * 0.68)
cy = int(h * 0.60)
x1, y1 = cx - 100, cy - 100
x2, y2 = cx + 100, cy + 100

crop = img.crop((x1, y1, x2, y2))
# Scale up 3x for clear viewing
scale = 3
c1 = crop.resize((crop.width * scale, crop.height * scale), Image.LANCZOS)
draw = ImageDraw.Draw(c1)

# Let's draw crosses at various coordinates relative to original image:
candidates = {
    "curr_0.642_0.597": (0.642, 0.597, (255, 0, 0)),    # Red
    "test_0.680_0.600": (0.680, 0.600, (0, 255, 0)),    # Green
    "test_0.685_0.600": (0.685, 0.600, (0, 0, 255)),    # Blue
    "test_0.690_0.600": (0.690, 0.600, (255, 255, 0)),  # Yellow
    "test_0.685_0.610": (0.685, 0.610, (255, 0, 255)),  # Magenta
    "test_0.690_0.610": (0.690, 0.610, (0, 255, 255)),  # Cyan
}

for name, (rx, ry, color) in candidates.items():
    # Map to crop coordinates
    px = (int(w * rx) - x1) * scale
    py = (int(h * ry) - y1) * scale
    if 0 <= px < c1.width and 0 <= py < c1.height:
        # Draw cross
        draw.line([(px - 8, py), (px + 8, py)], fill=color, width=2)
        draw.line([(px, py - 8), (px, py + 8)], fill=color, width=2)
        draw.text((px + 5, py + 5), name, fill=color)

# Save the crop
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/actual_cigarette_candidates.png'
c1.save(dest_path)
print(f"Saved actual cigarette comparison crop to {dest_path}")
