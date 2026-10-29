#!/usr/bin/env python3
import os
from PIL import Image, ImageFont, ImageDraw

def main():
    font_path = '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf'
    font37 = ImageFont.truetype(font_path, 37)
    font27 = ImageFont.truetype(font_path, 27)

    orig = 'Direction de la Législation Fiscale'

    # Calculate width
    x = 1.0
    for i, c in enumerate(orig):
        if (i == 0 and c == 'D') or (i == 16 and c == 'L') or (i == 28 and c == 'F'):
            f = font37
            ch = c
        else:
            f = font27
            ch = c.upper()
        x += f.getlength(ch)

    img_w = int(round(x)) + 1
    img = Image.new('RGB', (img_w, 39), 'white')
    draw = ImageDraw.Draw(img)

    # 1px border around image
    draw.rectangle([0, 0, img_w - 1, 38], outline='black', width=1)

    # Baseline 32 (aligns text vertically within 39px height)
    baseline = 32
    curr_x = 1.0
    for i, c in enumerate(orig):
        if (i == 0 and c == 'D') or (i == 16 and c == 'L') or (i == 28 and c == 'F'):
            f = font37
            ch = c
        else:
            f = font27
            ch = c.upper()

        draw.text((curr_x, baseline), ch, font=f, fill='black', anchor='ls')
        curr_x += f.getlength(ch)

    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_path = os.path.join(script_dir, 'dlf.gif')
    img.save(output_path, format='GIF')
    print(f"Generated {output_path}")

if __name__ == '__main__':
    main()
