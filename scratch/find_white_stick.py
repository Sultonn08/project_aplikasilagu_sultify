from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's scan a grid from x = 60% to 75%, y = 50% to 70% in the original image
# We print out the RGB values and brightness to find the cigarette stick
print("RGB Grid of lake_guy.png (x: 60% to 72%, y: 55% to 65%):")
for y_pct in range(54, 66):
    y = int(h * y_pct / 100)
    row = []
    for x_pct in range(60, 73):
        x = int(w * x_pct / 100)
        r, g, b = img.getpixel((x, y))
        brightness = (r + g + b) // 3
        # Format as a string of brightness or R-G-B characteristics
        # Let's print out R value scaled to 0-9
        r_scale = min(9, r // 25)
        row.append(str(r_scale))
    print(f"{y_pct}%: " + " ".join(row))
