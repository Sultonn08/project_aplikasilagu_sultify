from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# The cigarette stick is clearly visible at y: ~52-53%
# It's a horizontal white line. The TIP (left end) is what we need.
# Let's scan along y=52% and find where the bright horizontal line STARTS (leftmost bright pixel)

scan_y = int(h * 0.523)  # scan at y = 52.3%

print(f"Scanning row at y={scan_y} (y_rel={scan_y/h:.3f})")
print("Scanning from x=30% to x=70% looking for the bright cigarette line...")

bright_x = None
for x_px in range(int(w * 0.30), int(w * 0.70)):
    r, g, b = img.getpixel((x_px, scan_y))
    brightness = (r + g + b) / 3.0
    if brightness > 100:
        if bright_x is None:
            bright_x = x_px
            print(f"First bright pixel at x={x_px} ({x_px/w:.4f}), RGB=({r},{g},{b}), brightness={brightness:.1f}")
    else:
        if bright_x is not None:
            last_bright_x = x_px - 1
            r2, g2, b2 = img.getpixel((last_bright_x, scan_y))
            print(f"Bright line from x={bright_x} ({bright_x/w:.4f}) to x={last_bright_x} ({last_bright_x/w:.4f})")
            bright_x = None

# Let's also scan a few rows around y=52%
print("\nScanning multiple rows:")
for y_pct in range(50, 57):
    y_px = int(h * y_pct / 100)
    row_data = []
    for x_pct in range(35, 70, 1):
        x_px = int(w * x_pct / 100)
        r, g, b = img.getpixel((x_px, y_px))
        brightness = (r + g + b) / 3.0
        if brightness > 100:
            row_data.append(f"x={x_pct}%({brightness:.0f})")
    print(f"y={y_pct}%: {', '.join(row_data) if row_data else 'none'}")
