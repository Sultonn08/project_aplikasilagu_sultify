from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png')
w, h = img.size
print(f"Dimensions: {w}x{h}")

# Let's save a cropped region around the person to check it manually or inspect the values
# The person is on the right, around x: 0.55 to 0.85, y: 0.50 to 0.90
crop_x1 = int(w * 0.58)
crop_y1 = int(h * 0.55)
crop_x2 = int(w * 0.72)
crop_y2 = int(h * 0.75)

cropped = img.crop((crop_x1, crop_y1, crop_x2, crop_y2))
cropped.save('c:/laragon/www/my_vibe/scratch/person_crop.png')
print(f"Saved crop of size {cropped.size} to scratch/person_crop.png")

# Let's find local brightness peaks (e.g. the cigarette tip) in this crop
# The cigarette tip is usually a small white/orange spot. Let's find the brightest pixel.
brightest = None
max_b = -1
for cy in range(crop_y1, crop_y2):
    for cx in range(crop_x1, crop_x2):
        r, g, b = img.getpixel((cx, cy))[:3]
        brightness = (r + g + b) / 3.0
        # If it's a small ember, it might also have higher red/yellow hue. Let's check:
        if brightness > max_b:
            max_b = brightness
            brightest = (cx, cy, r, g, b)

print(f"Brightest pixel in person crop: x={brightest[0]} ({brightest[0]/w:.4f}), y={brightest[1]} ({brightest[1]/h:.4f}), rgb={brightest[2:5]}, brightness={max_b}")
