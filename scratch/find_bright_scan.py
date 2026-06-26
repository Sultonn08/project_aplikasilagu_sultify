from PIL import Image, ImageDraw

# Load the original background image
img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size
print(f"Image size: {w}x{h}")

# Let's scan the image for the brightest/most distinct pixel in the cigarette area
# The cigarette should show up as a slightly brighter line against the dark silhouette

# We know the man is roughly at x: 50-100%, y: 40-100%
# The cigarette extends from the hand towards the left
# Let's scan more broadly: x: 55-75%, y: 40-65%

# Find pixels that are distinctly brighter than their surroundings in the arm/hand area
# These would be the white cigarette stick
bright_candidates = []

for y_px in range(int(h * 0.42), int(h * 0.62)):
    for x_px in range(int(w * 0.52), int(w * 0.74)):
        r, g, b = img.getpixel((x_px, y_px))
        brightness = (r + g + b) / 3.0
        
        # Check if this pixel is notably brighter than dark surroundings
        # The cigarette stick would be white/gray against dark background
        if brightness > 60:  # Any non-dark pixel in the person area
            bright_candidates.append((x_px, y_px, r, g, b, brightness))

# Sort by brightness descending
bright_candidates.sort(key=lambda x: x[5], reverse=True)

print(f"Found {len(bright_candidates)} non-dark pixels in scan area")
print("Top 20 brightest pixels:")
for p in bright_candidates[:20]:
    print(f"  x={p[0]} ({p[0]/w:.3f}), y={p[1]} ({p[1]/h:.3f}), rgb=({p[2]},{p[3]},{p[4]}), brightness={p[5]:.1f}")

# Let's draw these on a crop to visualize them
crop_x1, crop_y1 = int(w * 0.50), int(h * 0.40)
crop_x2, crop_y2 = int(w * 0.76), int(h * 0.65)

crop = img.crop((crop_x1, crop_y1, crop_x2, crop_y2))
draw = ImageDraw.Draw(crop)

# Draw the top 5 brightest points
colors = [(255, 0, 0), (0, 255, 0), (0, 0, 255), (255, 255, 0), (255, 0, 255)]
for i, p in enumerate(bright_candidates[:5]):
    # Map to crop coordinates
    px = p[0] - crop_x1
    py = p[1] - crop_y1
    draw.ellipse([(px-3, py-3), (px+3, py+3)], outline=colors[i], width=1)
    draw.text((px + 4, py), f"#{i+1}: ({p[0]/w:.3f},{p[1]/h:.3f})", fill=colors[i])

# Save
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/bright_pixels_scan.png'
crop.save(dest_path)
print(f"\nSaved scan result to {dest_path}")
