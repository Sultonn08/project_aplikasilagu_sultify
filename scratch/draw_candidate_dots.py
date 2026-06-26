from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a 250x250 area centered at x: 67%, y: 56%
cx = int(w * 0.67)
cy = int(h * 0.56)
x1, y1 = cx - 125, cy - 125
x2, y2 = cx + 125, cy + 125

crop = img.crop((x1, y1, x2, y2))
draw = ImageDraw.Draw(crop)

# Let's draw crosses at various coordinates relative to original image:
candidates = {
    "orig": (0.720, 0.470, (255, 0, 0)),        # Red
    "exact_match": (0.6816, 0.4941, (0, 255, 0)), # Green
    "silhouette": (0.6475, 0.5918, (0, 0, 255)),  # Blue
    "candidate_new_1": (0.630, 0.550, (255, 255, 0)), # Yellow
    "candidate_new_2": (0.620, 0.565, (255, 0, 255)), # Magenta
    "candidate_new_3": (0.610, 0.580, (0, 255, 255)), # Cyan
}

for name, (rx, ry, color) in candidates.items():
    # Map to crop coordinates
    px = int(w * rx) - x1
    py = int(h * ry) - y1
    # Draw cross
    draw.line([(px - 8, py), (px + 8, py)], fill=color, width=2)
    draw.line([(px, py - 8), (px, py + 8)], fill=color, width=2)
    draw.text((px + 5, py + 5), name, fill=color)

# Save the crop
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/candidates_comparison.png'
crop.save(dest_path)
print(f"Saved comparison crop to {dest_path}")
