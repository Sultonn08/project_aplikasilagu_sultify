from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop the matched area
match_x = 642
match_y = 476
match_w = 102
match_h = 78

cropped = img.crop((match_x, match_y, match_x + match_w, match_y + match_h))
cropped.save('c:/laragon/www/my_vibe/scratch/matched_crop_actual.png')
print("Saved matched_crop_actual.png")

# Now let's find the brightest/most-red-orange pixel in this region
# Let's list the top brightest pixels in this region
pixels = []
for dy in range(match_h):
    for dx in range(match_w):
        x = match_x + dx
        y = match_y + dy
        r, g, b = img.getpixel((x, y))
        brightness = (r + g + b) / 3.0
        # Ember is red/orange. Let's look for red channel bias or overall brightness.
        # Let's print out the pixel value and its score
        score = r * 1.5 - g - b # red bias
        pixels.append((x, y, r, g, b, brightness, score))

# Sort by brightness
pixels.sort(key=lambda p: p[5], reverse=True)
print("Top 5 brightest pixels in the matched region:")
for i in range(5):
    x, y, r, g, b, br, sc = pixels[i]
    print(f"  {i}: x={x} ({x/w:.4f}), y={y} ({y/h:.4f}), rgb=({r},{g},{b}), brightness={br:.1f}, red_bias={sc:.1f}")

# Sort by red bias
pixels.sort(key=lambda p: p[6], reverse=True)
print("Top 5 red-biased pixels in the matched region:")
for i in range(5):
    x, y, r, g, b, br, sc = pixels[i]
    print(f"  {i}: x={x} ({x/w:.4f}), y={y} ({y/h:.4f}), rgb=({r},{g},{b}), brightness={br:.1f}, red_bias={sc:.1f}")
