from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

x1, y1 = int(w*0.53), int(h*0.48)
x2, y2 = int(w*0.70), int(h*0.65)
crop1 = img.crop((x1, y1, x2, y2))
c1 = crop1.resize((crop1.width * 3, crop1.height * 3), Image.LANCZOS)

draw = ImageDraw.Draw(c1)

# More candidates focused on where I can see the ember in the zoomed image
# Based on visual: tip of the cigarette is at approximately where the glowing dot is
# In the zoomed image the cigarette tip looks like it's at x~63%, y~57%
candidates = [
    (0.625, 0.570, "E(0.625,0.57)", (255, 165, 0)),   # Orange
    (0.630, 0.565, "F(0.63,0.565)", (255, 0, 255)),   # Magenta
    (0.635, 0.560, "G(0.635,0.56)", (0, 255, 255)),   # Cyan
    (0.640, 0.555, "H(0.64,0.555)", (255, 255, 255)), # White
    (0.620, 0.575, "I(0.62,0.575)", (255, 255, 0)),   # Yellow
]

for rx, ry, name, color in candidates:
    px = (int(w * rx) - x1) * 3
    py = (int(h * ry) - y1) * 3
    if 0 <= px < c1.width and 0 <= py < c1.height:
        draw.line([(px-8, py), (px+8, py)], fill=color, width=2)
        draw.line([(px, py-8), (px, py+8)], fill=color, width=2)
        draw.text((px+5, py-12), name, fill=color)

dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/hand_zoom_refined2.png'
c1.save(dest_path)
print(f"Saved to {dest_path}")
