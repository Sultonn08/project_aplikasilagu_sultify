from PIL import Image

# Load the image
img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png')
w, h = img.size
print(f"Image dimensions: {w}x{h}")

best_pixel = None
best_val = -9999

for y in range(int(h * 0.3), int(h * 0.8)):
    for x in range(int(w * 0.5), int(w * 0.9)):
        r, g, b = img.getpixel((x, y))[:3]
        # Red-orange metric: high R, R - G > 30, R - B > 30
        # Let's also look for bright glowing orange
        metric = int(r) * 2 - int(g) - int(b)
        if metric > best_val:
            best_val = metric
            best_pixel = (x, y, r, g, b)

if best_pixel:
    x, y, r, g, b = best_pixel
    rel_x = x / w
    rel_y = y / h
    print(f"Best ember candidate: x={x} ({rel_x:.4f}), y={y} ({rel_y:.4f}), color=({r},{g},{b}), metric={best_val}")
    
    # Also print any other interesting orange/red pixels in a list to see if there is another cluster
    candidates = []
    for y in range(int(h * 0.3), int(h * 0.8)):
        for x in range(int(w * 0.5), int(w * 0.9)):
            r, g, b = img.getpixel((x, y))[:3]
            if r > 120 and g > 40 and r - g > 40 and g - b > 10:
                candidates.append((x, y, r, g, b))
    
    print(f"Total orange candidates: {len(candidates)}")
    if len(candidates) > 0:
        # Print top 5 unique-ish candidates
        candidates.sort(key=lambda item: int(item[2])*2 - item[3] - item[4], reverse=True)
        for i, cand in enumerate(candidates[:10]):
            cx, cy, cr, cg, cb = cand
            print(f"  Candidate {i}: x={cx} ({cx/w:.4f}), y={cy} ({cy/h:.4f}), color=({cr},{cg},{cb})")
else:
    print("No candidate found")
