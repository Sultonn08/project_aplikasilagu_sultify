from PIL import Image

img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Let's crop a 60x60 region centered at x=681, y=507
cx, cy = 681, 507
crop_r = 30
cropped = img.crop((cx - crop_r, cy - crop_r, cx + crop_r, cy + crop_r))
cropped.save('c:/laragon/www/my_vibe/scratch/ember_check.png')
print(f"Saved ember_check.png centered at {cx}, {cy}")
