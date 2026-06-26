from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

cx = 657
cy = 611

# Print the RGB values of pixels in a 20x20 area around the current tip
# Current is (657, 611) which is x=0.6416, y=0.5967.
# Let's print from x=645 to 665, y=600 to 620.
print("RGB values around the cigarette tip:")
print("      " + "  ".join(f"{x:3d}" for x in range(645, 666)))
for y in range(600, 621):
    row_strs = []
    for x in range(645, 666):
        r, g, b = img.getpixel((x, y))
        # Format as R,G,B or just R value if we want to save space
        # Since we want to see where the light cigarette is, let's print the R value
        row_strs.append(f"{r:3d}")
    print(f"{y:3d}: " + " ".join(row_strs))
