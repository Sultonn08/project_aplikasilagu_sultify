from PIL import Image

# Template: media__1782488384063.jpg (102x78)
template = Image.open('C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782488384063.jpg').convert('RGB')
tw, th = template.size

# Search: lake_guy.png (1024x1024)
search_img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
sw, sh = search_img.size

# We want to find the exact top-left (x, y) in search_img that minimizes the Mean Absolute Error (MAE)
# with the template.
best_mae = 1e9
best_x, best_y = 0, 0

# Narrow search region around the previous match:
for y in range(450, 500):
    for x in range(620, 660):
        mae = 0
        for ty in range(th):
            for tx in range(tw):
                tc = template.getpixel((tx, ty))
                sc = search_img.getpixel((x + tx, y + ty))
                mae += sum(abs(tc[i] - sc[i]) for i in range(3))
        mae /= (tw * th * 3)
        if mae < best_mae:
            best_mae = mae
            best_x, best_y = x, y

print(f"Exact best matching top-left: x={best_x}, y={best_y} with MAE={best_mae:.4f}")
# The user's crop ember center is at x=39, y=31 relative to the top-left of the crop.
# So in lake_guy.png, the ember is at:
ember_x = best_x + 39
ember_y = best_y + 31
print(f"Ember in lake_guy.png: x={ember_x} ({ember_x/sw:.4f}), y={ember_y} ({ember_y/sh:.4f})")
