from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let me view the actual cropped image area to find the cigarette by visual inspection
# The hand is visible around x=65-70%, y=57-62%
# The cigarette extends UP-LEFT from the hand
# Let me create a series of zoom views

# View 1: Crop of x:53-70%, y:48-65% (hand + cigarette area)
x1, y1 = int(w*0.53), int(h*0.48)
x2, y2 = int(w*0.70), int(h*0.65)
crop1 = img.crop((x1, y1, x2, y2))
# Scale up 3x
c1 = crop1.resize((crop1.width * 3, crop1.height * 3), Image.LANCZOS)

draw = ImageDraw.Draw(c1)
# Add candidate points
candidates = [
    (0.580, 0.590, "A(0.58,0.59)", (255, 0, 0)),
    (0.597, 0.573, "B(0.60,0.57)", (0, 255, 0)),
    (0.615, 0.560, "C(0.62,0.56)", (0, 0, 255)),
    (0.560, 0.580, "D(0.56,0.58)", (255, 255, 0)),
]

for rx, ry, name, color in candidates:
    px = (int(w * rx) - x1) * 3
    py = (int(h * ry) - y1) * 3
    if 0 <= px < c1.width and 0 <= py < c1.height:
        draw.line([(px-8, py), (px+8, py)], fill=color, width=2)
        draw.line([(px, py-8), (px, py+8)], fill=color, width=2)
        draw.text((px+5, py-12), name, fill=color)

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/hand_zoom_marked.png'
c1.save(dest_path)
print(f"Saved to {dest_path}")
