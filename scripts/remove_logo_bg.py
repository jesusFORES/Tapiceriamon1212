from PIL import Image
from collections import Counter

src = r'C:\Users\JESUS\Desktop\tapiceria\Gemini_Generated_Image_ap7t6tap7t6tap7t.jpeg'
out = r'C:\Users\JESUS\Desktop\tapiceria\public\Gemini_Generated_Image_ap7t6tap7t6tap7t_transparent.png'

img = Image.open(src).convert('RGBA')
pixels = list(img.getdata())

# Find the dominant background color (usually the cyan block behind the logo)
color_counts = Counter(p for p in pixels if p[:3] != (255, 255, 255))
background = color_counts.most_common(1)[0][0][:3]

new_pixels = []
for r, g, b, a in pixels:
    if abs(r - background[0]) <= 35 and abs(g - background[1]) <= 35 and abs(b - background[2]) <= 35:
        new_pixels.append((255, 255, 255, 0))
    else:
        new_pixels.append((r, g, b, a))

img.putdata(new_pixels)
img.save(out, format='PNG')
print(f'Generated transparent logo: {out}')
print(f'Background color removed: {background}')
