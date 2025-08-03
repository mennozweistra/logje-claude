#!/usr/bin/env python3
"""
Simple PNG icon generator for Logje Health Tracker PWA
Creates proper PNG icons that Safari and Brave browsers require for PWA installation
"""

try:
    from PIL import Image, ImageDraw, ImageFont
    print("Creating PNG icons for PWA compatibility...")
    
    # Icon sizes required for PWA
    sizes = [48, 72, 96, 144, 192, 512]
    
    for size in sizes:
        # Create a new image with transparent background
        img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        draw = ImageDraw.Draw(img)
        
        # Background with rounded corners (dark theme color)
        background_color = (31, 41, 55, 255)  # #1f2937
        draw.rounded_rectangle([0, 0, size, size], radius=size//8, fill=background_color)
        
        # Health cross symbol (white)
        cross_color = (255, 255, 255, 255)
        cross_size = size // 2.5
        cross_thickness = size // 12
        center_x, center_y = size // 2, size // 2
        
        # Horizontal bar
        draw.rectangle([
            center_x - cross_size//2, center_y - cross_thickness//2,
            center_x + cross_size//2, center_y + cross_thickness//2
        ], fill=cross_color)
        
        # Vertical bar
        draw.rectangle([
            center_x - cross_thickness//2, center_y - cross_size//2,
            center_x + cross_thickness//2, center_y + cross_size//2
        ], fill=cross_color)
        
        # Small heart symbol (red)
        heart_color = (239, 68, 68, 255)  # #ef4444
        heart_size = size // 8
        heart_x = center_x + cross_size//4
        heart_y = center_y + cross_size//4
        
        # Simple heart using circles
        draw.ellipse([
            heart_x - heart_size//2, heart_y - heart_size//2,
            heart_x + heart_size//2, heart_y + heart_size//2
        ], fill=heart_color)
        
        # Save the icon
        filename = f'public/images/icons/icon-{size}x{size}.png'
        img.save(filename, 'PNG')
        print(f"Created {filename}")
    
    print("All PNG icons created successfully!")
    
except ImportError:
    print("PIL (Pillow) not available. Creating basic icon files...")
    
    # Fallback: Create empty PNG files that can be replaced manually
    # This is a minimal 1x1 transparent PNG in base64
    minimal_png = b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\nIDATx\x9cc\x00\x01\x00\x00\x05\x00\x01\r\n-\xdb\x00\x00\x00\x00IEND\xaeB`\x82'
    
    sizes = [48, 72, 96, 144, 192, 512]
    for size in sizes:
        filename = f'public/images/icons/icon-{size}x{size}.png'
        with open(filename, 'wb') as f:
            f.write(minimal_png)
        print(f"Created basic {filename} (replace with proper icon)")
    
    print("Basic icon files created. Please replace with proper icons using an image editor.")

print("\nNext steps:")
print("1. Update manifest.json to use PNG icons")
print("2. Add proper icon references to layout files")
print("3. Test PWA installation on Safari and Brave")