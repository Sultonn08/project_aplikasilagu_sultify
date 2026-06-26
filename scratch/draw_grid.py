from PIL import Image, ImageDraw, ImageFont

# Load the background image
img = Image.open('c:/laragon/www/my_vibe/assets/images/lake_guy.png').convert('RGB')
w, h = img.size

# Create a copy to draw on
draw_img = img.copy()
draw = ImageDraw.Draw(draw_img)

# Draw grid lines every 5% of width/height
for pct in range(5, 100, 5):
    # Vertical line
    x = int(w * pct / 100)
    draw.line([(x, 0), (x, h)], fill=(255, 0, 0), width=1)
    # Horizontal line
    y = int(h * pct / 100)
    draw.line([(0, y), (w, y)], fill=(255, 0, 0), width=1)
    
    # Text label for X
    draw.text((x + 2, 10), f"{pct}%", fill=(255, 255, 0))
    # Text label for Y
    draw.text((10, y + 2), f"{pct}%", fill=(255, 255, 0))

# Also draw grid labels at intersections in the person region (x: 55%-85%, y: 45%-85%)
for x_pct in range(55, 86, 5):
    for y_pct in range(45, 86, 5):
        x = int(w * x_pct / 100)
        y = int(h * y_pct / 100)
        draw.text((x + 2, y + 2), f"{x_pct},{y_pct}", fill=(0, 255, 255))

# Save the grid image to the artifacts directory
grid_path = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/70d14234-39af-44f8-a996-b4b7f4ef5622/lake_guy_grid.png'
draw_img.save(grid_path)
print(f"Saved grid image to {grid_path}")
