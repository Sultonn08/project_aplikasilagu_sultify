from PIL import Image

img_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782490376579.jpg'
img = Image.open(img_path).convert('RGB')
w, h = img.size

# Let's print out the actual RGB values of a grid around x=18, y=26
print("Grid of pixel RGB values around the ember:")
# Let's show x from 5 to 60, y from 10 to 35
for y in range(10, 35):
    row = []
    for x in range(5, 60):
        r, g, b = img.getpixel((x, y))
        # Find if it's the ember (approx x=18, y=26)
        if abs(x - 18) <= 2 and abs(y - 26) <= 2:
            row.append('E') # Ember
        # If it's a very dark pixel (the hand/sleeve silhouette)
        elif r < 40 and g < 40 and b < 40:
            row.append('#')
        # If it is a relatively bright pixel in a dark scene (possible cigarette stick or background fog)
        elif (r + g + b) / 3.0 > 60:
            row.append('o')
        else:
            row.append('.')
    print(f"{y:2d}: " + "".join(row))
