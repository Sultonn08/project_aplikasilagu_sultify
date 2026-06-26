from PIL import Image, ImageDraw

# This is the verification image I created earlier showing the man + ember marker
# From this image I can see the cigarette tip is at the upper-left
# Let me analyze the actual background image to pinpoint the cigarette tip more accurately

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# The man's hand holding the cigarette is clearly visible at upper area
# Based on visual inspection of the zoom image, the cigarette tip (white glowing end)
# appears to be at approximately:
# - x: ~64.5% from left of original image
# - y: ~59.0% from top of original image
# But that might be the hand holding, not the actual tip

# Let me scan around x=62-68%, y=55-62% for local brightness peaks
print("Fine scanning for cigarette tip:")
best_brightness = 0
best_pixel = None

for y_px in range(int(h * 0.55), int(h * 0.62)):
    for x_px in range(int(w * 0.60), int(w * 0.70)):
        r, g, b = img.getpixel((x_px, y_px))
        brightness = (r + g + b) / 3.0
        if brightness > best_brightness:
            best_brightness = brightness
            best_pixel = (x_px, y_px, r, g, b)

print(f"Brightest pixel in scan region:")
print(f"  x={best_pixel[0]} ({best_pixel[0]/w:.4f}), y={best_pixel[1]} ({best_pixel[1]/h:.4f})")
print(f"  RGB=({best_pixel[2]},{best_pixel[3]},{best_pixel[4]}), brightness={best_brightness:.1f}")

# Now let's look at the actual cropped image showing the hand
# From the final_ember_position.png, I could see:
# - The orange marker was at (0.695, 0.665) = in the chest/body area
# - The actual cigarette tip (white end visible in top-left of that crop) 
#   was at roughly offset (-80, -90) from the crop center
# So tip = (0.695*1024 - 80 - 80, 0.665*1024 - 80 - 90) = (551, 511)
# Relative: (551/1024, 511/1024) = (0.538, 0.499)

# Let me verify this by checking that pixel's RGB
tip_x, tip_y = 551, 511
r, g, b = img.getpixel((tip_x, tip_y))
print(f"\nPixel at estimated tip (0.538, 0.499): RGB=({r},{g},{b}), brightness={(r+g+b)/3:.1f}")

# Let's also generate a new zoomed crop centered right on the cigarette tip
# From the hand zoom image, the cigarette was visible at upper-left
# Let's center on x=0.54, y=0.50 and zoom in tight
cx2 = int(w * 0.58)
cy2 = int(h * 0.53)
crop2 = img.crop((cx2 - 60, cy2 - 60, cx2 + 60, cy2 + 60))
c2 = crop2.resize((crop2.width * 5, crop2.height * 5), Image.LANCZOS)

draw = ImageDraw.Draw(c2)

# Mark a 5x5 grid of test points
for rx in [0.55, 0.56, 0.57, 0.58, 0.59, 0.60]:
    for ry in [0.50, 0.51, 0.52, 0.53, 0.54, 0.55]:
        px = (int(w * rx) - (cx2 - 60)) * 5
        py = (int(h * ry) - (cy2 - 60)) * 5
        if 0 <= px < c2.width and 0 <= py < c2.height:
            draw.point((px, py), fill=(255, 0, 0))

# Save
dest_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/tip_search.png'
c2.save(dest_path)
print(f"\nSaved tight crop to {dest_path}")
