from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's search for pixels that are bright or have some color in the right side of the image
# Let's scan x from 0.55 to 0.85, y from 0.45 to 0.85
# And print out candidates for the cigarette tip.
# Typically a cigarette tip is a small bright spot, maybe light orange, white, or red.
# Let's list any pixels that are noticeably brighter than their surroundings.
# We can compute a local contrast/brightness.
candidates = []
for y in range(int(h * 0.45), int(h * 0.85)):
    for x in range(int(w * 0.55), int(w * 0.85)):
        r, g, b = img.getpixel((x, y))
        # Let's check if the pixel is bright (e.g. R > 150, G > 100, B > 100)
        # or if it has some orange hue.
        # Since it's a dark background, a cigarette tip (even unlit or lit) might stand out.
        if r > 100 and g > 80:
            candidates.append((x, y, r, g, b))

print(f"Total bright/orange candidates: {len(candidates)}")
# Let's print out the top 20 candidates sorted by y
candidates.sort(key=lambda c: c[1]) # sort by Y coordinate
for i, c in enumerate(candidates[:30]):
    print(f"  {i}: x={c[0]} ({c[0]/w:.4f}), y={c[1]} ({c[1]/h:.4f}), rgb=({c[2]},{c[3]},{c[4]})")
