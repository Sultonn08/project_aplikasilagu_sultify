from PIL import Image, ImageDraw

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# The cigarette is clearly visible at y: 52-56%, with bright pixels around x: 66-69%
# But these may be body area pixels, not the cigarette tip
# 
# Let me zoom into y=51-56%, x=45-70% and look specifically for the WHITE stick
# Let me look at a BRIGHTER threshold (>120) to find just the white parts

print("High-brightness pixels (>120) in the area y=46-62%, x=40-75%:")
cig_pixels = []
for y_pct in range(46, 62):
    y_px = int(h * y_pct / 100)
    for x_pct in range(40, 75):
        x_px = int(w * x_pct / 100)
        r, g, b = img.getpixel((x_px, y_px))
        brightness = (r + g + b) / 3.0
        if brightness > 120:
            cig_pixels.append((x_px, y_px, brightness, r, g, b))

cig_pixels.sort(key=lambda x: x[2], reverse=True)
print(f"Found {len(cig_pixels)} bright pixels (>120)")

# Let's see what the unique rows are and their brightest pixels
rows = {}
for p in cig_pixels:
    y_pct_approx = int(p[1] / h * 100)
    if y_pct_approx not in rows:
        rows[y_pct_approx] = []
    rows[y_pct_approx].append(p)

for y_pct in sorted(rows.keys()):
    pixels = rows[y_pct]
    x_vals = [p[0]/w for p in pixels]
    brightest = max(pixels, key=lambda x: x[2])
    print(f"y={y_pct}%: {len(pixels)} bright pixels, x_range=[{min(x_vals):.3f} - {max(x_vals):.3f}], brightest at x={brightest[0]/w:.4f} (b={brightest[2]:.1f}, rgb={brightest[3:][:3]})")

# Also find the overall leftmost bright pixel (that's the tip of the cigarette)
# The cigarette stick is white/light gray and extends HORIZONTALLY
# Look for a cluster of bright pixels at a consistent y level
# The leftmost x of this cluster is the TIP

# Group by rows with at least 3 consecutive bright pixels
print("\nLooking for the cigarette stick (horizontal bright line):")
for y_pct in range(48, 60):
    y_px = int(h * y_pct / 100)
    bright_xs = []
    for x_pct in range(38, 76):
        x_px = int(w * x_pct / 100)
        r, g, b = img.getpixel((x_px, y_px))
        brightness = (r + g + b) / 3.0
        if brightness > 110:
            bright_xs.append(x_pct)
    if len(bright_xs) >= 3:
        # Find consecutive runs
        runs = []
        start = bright_xs[0]
        prev = bright_xs[0]
        for xp in bright_xs[1:]:
            if xp > prev + 2:
                runs.append((start, prev))
                start = xp
            prev = xp
        runs.append((start, prev))
        for run in runs:
            if run[1] - run[0] >= 3:  # At least 3% wide = actual line
                print(f"  y={y_pct}%: LINE from x={run[0]}% to x={run[1]}%, TIP at x={run[0]}%")
