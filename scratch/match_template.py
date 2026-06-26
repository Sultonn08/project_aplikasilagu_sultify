from PIL import Image

# Template: media__1782488384063.jpg (the crop of the cigarette tip)
template = Image.open('C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/media__1782488384063.jpg').convert('RGB')
tw, th = template.size

# Search in: lake_guy.png (the background image of the page)
search_img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
sw, sh = search_img.size

print(f"Template size: {tw}x{th}")
print(f"Search image size: {sw}x{sh}")

# Since doing a full pixel-by-pixel search for a large image can be slow in pure Python,
# let's sub-sample or search in a logical area (the bottom right / middle region where the guy sits)
# In lake_guy.png: x from 0.5 to 0.85, y from 0.4 to 0.8
x_start = int(sw * 0.5)
x_end = int(sw * 0.9) - tw
y_start = int(sh * 0.35)
y_end = int(sh * 0.85) - th

# Let's take a small set of sample pixels from the template to find fast candidate locations
# e.g., 5 random or spaced pixels in the template
sample_points = [
    (0, 0), (tw//2, th//2), (tw-1, th-1), (tw//4, th//4), (3*tw//4, 3*th//4)
]
sample_colors = [template.getpixel(p) for p in sample_points]

best_sad = 1e9
best_loc = (0, 0)

# Fast screening pass
for y in range(y_start, y_end, 2):
    for x in range(x_start, x_end, 2):
        # check sample points first
        match = True
        sad = 0
        for (px, py), tc in zip(sample_points, sample_colors):
            sc = search_img.getpixel((x + px, y + py))
            diff = sum(abs(tc[i] - sc[i]) for i in range(3))
            sad += diff
            if diff > 60: # threshold for fast fail
                match = False
                break
        if not match:
            continue
        
        # if sample points match, do a full or denser check of the area
        full_sad = 0
        # Check every 4th pixel for speed
        for ty in range(0, th, 4):
            for tx in range(0, tw, 4):
                tc = template.getpixel((tx, ty))
                sc = search_img.getpixel((x + tx, y + ty))
                full_sad += sum(abs(tc[i] - sc[i]) for i in range(3))
        
        if full_sad < best_sad:
            best_sad = full_sad
            best_loc = (x, y)

print(f"Best match in lake_guy.png: x={best_loc[0]}, y={best_loc[1]}, sad={best_sad}")
rel_x = (best_loc[0] + tw/2) / sw
rel_y = (best_loc[1] + th/2) / sh
print(f"Relative center of matched crop: x_rel={rel_x:.4f}, y_rel={rel_y:.4f}")
print(f"Relative top-left of matched crop: x_rel_tl={best_loc[0]/sw:.4f}, y_rel_tl={best_loc[1]/sh:.4f}")
