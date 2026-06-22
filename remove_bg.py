import sys
from PIL import Image

def remove_black_bg(input_path, output_path):
    img = Image.open(input_path).convert("RGBA")
    datas = img.getdata()
    
    newData = []
    for item in datas:
        # Get RGB values
        r, g, b, a = item
        
        # Calculate brightness (simple average or luminance)
        lum = (r + g + b) / 3
        
        # If the pixel is very dark (close to black), make it transparent
        if lum < 15: # threshold for "black"
            newData.append((r, g, b, 0)) # Fully transparent
        elif lum < 50:
            # Semi-transparent for anti-aliasing edges
            alpha = int((lum - 15) / 35 * 255)
            newData.append((r, g, b, alpha))
        else:
            newData.append((r, g, b, 255))
            
    img.putdata(newData)
    img.save(output_path, "PNG")

if __name__ == "__main__":
    remove_black_bg(sys.argv[1], sys.argv[2])
