from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# From the wide grid, I can see the cigarette in the man's hand.
# The man's hand is at approximately x: 58-62%, y: 55-60%
# The cigarette extends from his hand going UP and to the LEFT
# The tip would be at approximately x: 52-56%, y: 49-53%

# Let me make a clear, zoomed-in crop of that hand + cigarette area
cx_pct, cy_pct = 57, 53
cx = int(w * cx_pct / 100)
cy = int(h * cy_pct / 100)
crop_size = 60  # small tight crop

x1, y1 = max(0, cx - crop_size), max(0, cy - crop_size)
x2, y2 = min(w, cx + crop_size), min(h, cy + crop_size)

crop = img.crop((x1, y1, x2, y2))

# Scale up 3x for visibility
crop_scaled = crop.resize((crop.width * 4, crop.height * 4), Image.NEAREST)
cw, ch = crop_scaled.size
draw = ImageDraw.Draw(crop_scaled)

# Draw grid every 1% 
for x_pct in range(46, 70, 1):
    cx_pct_px = (int(w * x_pct / 100) - x1) * 4
    if 0 <= cx_pct_px < cw:
        color = (255, 0, 0) if x_pct % 5 == 0 else (100, 0, 0)
        draw.line([(cx_pct_px, 0), (cx_pct_px, ch)], fill=color, width=1)
        if x_pct % 2 == 0:
            draw.text((cx_pct_px + 1, 3), f"{x_pct}%", fill=(255, 100, 100))

for y_pct in range(44, 67, 1):
    cy_pct_px = (int(h * y_pct / 100) - y1) * 4
    if 0 <= cy_pct_px < ch:
        color = (0, 255, 0) if y_pct % 5 == 0 else (0, 100, 0)
        draw.line([(0, cy_pct_px), (cw, cy_pct_px)], fill=color, width=1)
        if y_pct % 2 == 0:
            draw.text((3, cy_pct_px + 1), f"{y_pct}%", fill=(100, 255, 100))

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/hand_cig_zoom.png'
crop_scaled.save(dest_path)
print(f"Saved to {dest_path}")
