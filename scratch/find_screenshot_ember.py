from PIL import Image

# Path to the user's screenshot
img_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782490376579.jpg'

try:
    img = Image.open(img_path).convert('RGB')
    w, h = img.size
    print(f"Screenshot size: {w}x{h}")
    
    # Let's find orange/red pixels (which belong to the ember glow)
    # The ember glow has orange/red: R should be high, G medium, B low.
    # e.g., R > 150, G < 150, B < 80
    ember_pixels = []
    for y in range(h):
        for x in range(w):
            r, g, b = img.getpixel((x, y))
            # Relax orange condition a bit if needed, or find the pixel closest to pure orange
            if r > 150 and g > 80 and b < 100:
                ember_pixels.append((x, y, r, g, b))
                
    print(f"Found {len(ember_pixels)} orange/red pixels")
    
    # Let's print out the RGB grid of the entire crop to see where the glow is
    # and where the silhouette is.
    print("Full image color intensity grid:")
    for y in range(0, h, max(1, h // 25)):
        row = []
        for x in range(0, w, max(1, w // 40)):
            r, g, b = img.getpixel((x, y))
            # Orange glow
            if r > 140 and g > 70 and b < 80:
                row.append('*')
            # Light pixels (cigarette or smoke)
            elif (r + g + b) / 3.0 > 100:
                row.append('o')
            # Dark pixels (silhouette)
            elif (r + g + b) / 3.0 < 40:
                row.append('#')
            else:
                row.append('.')
        print("".join(row))
            
except Exception as e:
    print(f"Error: {e}")
