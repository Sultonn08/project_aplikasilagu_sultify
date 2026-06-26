from PIL import Image

# Analyze the user's latest screenshot
img_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/.tempmediaStorage/media_70d14234-39af-44f8-a996-b4b7f4ef5622_1782493083452.png'

try:
    img = Image.open(img_path).convert('RGB')
    w, h = img.size
    print(f"Screenshot size: {w}x{h}")
    
    # Find the orange/amber glow (ember of the cigarette)
    # Orange has high R, medium-high G, low B
    # AND should be brighter than surrounding dark pixels
    peaks = []
    for y in range(h):
        for x in range(w):
            r, g, b = img.getpixel((x, y))
            # Orange ember: R significantly higher than B, G moderate
            if r > 100 and r > b + 40 and r > g - 20:
                brightness = (r + g + b) / 3.0
                peaks.append((x, y, r, g, b, brightness, r - b))
    
    peaks.sort(key=lambda x: x[6], reverse=True)
    print(f"Found {len(peaks)} orange pixels")
    print("Top 10 most orange pixels:")
    for p in peaks[:10]:
        print(f"  x={p[0]} ({p[0]/w:.3f}), y={p[1]} ({p[1]/h:.3f}), RGB=({p[2]},{p[3]},{p[4]}), R-B={p[6]}")
    
    if peaks:
        # Find the cluster center of the orange pixels
        top_peaks = peaks[:20]
        avg_x = sum(p[0] for p in top_peaks) / len(top_peaks)
        avg_y = sum(p[1] for p in top_peaks) / len(top_peaks)
        print(f"\nEmber center in screenshot: x={avg_x:.1f}/{w} ({avg_x/w:.3f}), y={avg_y:.1f}/{h} ({avg_y/h:.3f})")

except FileNotFoundError:
    print("File not found - listing available media files...")
    import os
    media_dir = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/.tempmediaStorage'
    if os.path.exists(media_dir):
        files = sorted(os.listdir(media_dir))
        for f in files[-10:]:
            print(f)
