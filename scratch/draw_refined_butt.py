from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a tight 100x100 area around the cigarette tip:
cx = int(w * 0.6475)
cy = int(h * 0.5908)
x1, y1 = cx - 50, cy - 50
x2, y2 = cx + 50, cy + 50

crop = img.crop((x1, y1, x2, y2))
# Scale up 5x
scale = 5
c1 = crop.resize((crop.width * scale, crop.height * scale), Image.LANCZOS)
draw = ImageDraw.Draw(c1)

# Draw a crosshair at the exact peak (0.6475, 0.5908)
px = (cx - x1) * scale
py = (cy - y1) * scale

# Draw a red circle and cross at the target point
draw.ellipse([(px - 5, py - 5), (px + 5, py + 5)], outline=(255, 0, 0), width=2)
draw.line([(px - 15, py), (px + 15, py)], fill=(255, 0, 0), width=1)
draw.line([(px, py - 15), (px, py + 15)], fill=(255, 0, 0), width=1)

# Let's also draw the old coordinates (0.642, 0.597) in blue for comparison
old_cx = int(w * 0.642)
old_cy = int(h * 0.597)
old_px = (old_cx - x1) * scale
old_py = (old_cy - y1) * scale
draw.ellipse([(old_px - 5, old_py - 5), (old_px + 5, old_py + 5)], outline=(0, 0, 255), width=2)
draw.text((old_px + 7, old_py + 7), "OLD (0.642, 0.597)", fill=(0, 0, 255))
draw.text((px + 7, py - 15), "NEW (0.6475, 0.5908)", fill=(255, 0, 0))

# Save
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/refined_butt_verification.png'
c1.save(dest_path)
print(f"Saved refined butt verification image to {dest_path}")
