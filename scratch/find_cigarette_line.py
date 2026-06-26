from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's print the RGB values in a grid of x in [600, 650], y in [570, 610]
# to see if we can find the cigarette line.
# The cigarette is a bright line (white) on a darker background (the water is dark grey/blue, the man is dark silhouette).
# Let's print the brightness values as a grid of characters where higher brightness is represented by denser characters.

chars = " .:-=+*#%@"

for y in range(580, 615):
    line_chars = []
    for x in range(610, 655):
        r, g, b = img.getpixel((x, y))
        brightness = (r + g + b) // 3
        # Map 0-255 to 0-9
        idx = min(9, brightness // 25)
        line_chars.append(chars[idx])
    print(f"{y:3d} ({y/h:.4f}): " + "".join(line_chars))
