/**
 * Color Namer - Generate creative names for colors
 * Inspired by poetic color naming
 */

class ColorNamer {
    constructor() {
        // Hue-based names (0-360 degrees)
        this.hueNames = {
            red: [
                'sunset ember', 'crimson dream', 'ruby whisper', 'wine stain',
                'cherry blossom', 'firelight', 'autumn leaf', 'rose petal'
            ],
            orange: [
                'tangerine sky', 'honey glow', 'autumn spice', 'copper sunset',
                'burnt sienna', 'peach melba', 'clay earth', 'amber wave'
            ],
            yellow: [
                'lemon drop', 'golden hour', 'butter cream', 'sunny day',
                'canary song', 'desert sand', 'wheat field', 'honeycomb'
            ],
            green: [
                'forest moss', 'mint breeze', 'jade garden', 'sleepy forest',
                'ocean kelp', 'spring meadow', 'sage wisdom', 'lime zest'
            ],
            cyan: [
                'tropical water', 'ice mint', 'caribbean sea', 'fresh pool',
                'glacier melt', 'turquoise stone', 'ocean spray', 'aqua dream'
            ],
            blue: [
                'midnight sky', 'ocean depth', 'sapphire night', 'denim fade',
                'arctic ice', 'morning frost', 'navy fleet', 'bluebell field'
            ],
            purple: [
                'twilight hour', 'lavender field', 'royal velvet', 'plum shadow',
                'violet haze', 'grape vine', 'mystic night', 'orchid bloom'
            ],
            pink: [
                'cotton candy', 'peach sorbet', 'ballet slipper', 'rose quartz',
                'blush wine', 'cherry blossom', 'bubblegum pop', 'flamingo feather'
            ]
        };

        // Saturation modifiers
        this.saturationModifiers = {
            low: ['muted', 'dusty', 'faded', 'soft', 'pale', 'gentle'],
            medium: ['', '', '', ''], // Often no modifier needed
            high: ['vibrant', 'electric', 'bold', 'vivid', 'bright', 'intense']
        };

        // Lightness modifiers
        this.lightnessModifiers = {
            dark: ['deep', 'dark', 'shadowy', 'midnight', 'charcoal'],
            medium: ['', '', ''], // Often no modifier needed
            light: ['light', 'pale', 'soft', 'airy', 'whisper']
        };
    }

    /**
     * Get the hue category from HSL hue value (0-360)
     */
    getHueCategory(hue) {
        if (hue >= 345 || hue < 15) return 'red';
        if (hue >= 15 && hue < 45) return 'orange';
        if (hue >= 45 && hue < 75) return 'yellow';
        if (hue >= 75 && hue < 150) return 'green';
        if (hue >= 150 && hue < 195) return 'cyan';
        if (hue >= 195 && hue < 270) return 'blue';
        if (hue >= 270 && hue < 310) return 'purple';
        if (hue >= 310 && hue < 345) return 'pink';
        return 'blue'; // fallback
    }

    /**
     * Get saturation category
     */
    getSaturationCategory(saturation) {
        if (saturation < 25) return 'low';
        if (saturation < 60) return 'medium';
        return 'high';
    }

    /**
     * Get lightness category
     */
    getLightnessCategory(lightness) {
        if (lightness < 30) return 'dark';
        if (lightness < 70) return 'medium';
        return 'light';
    }

    /**
     * Generate a creative name for a color
     * @param {number} r - Red value (0-255)
     * @param {number} g - Green value (0-255)
     * @param {number} b - Blue value (0-255)
     * @returns {string} Creative color name
     */
    nameColor(r, g, b) {
        // Convert to HSL
        const hsl = this.rgbToHsl(r, g, b);

        // Handle grayscale colors (only very desaturated colors)
        if (hsl.s < 5) {
            if (hsl.l < 15) return 'Void Black';
            if (hsl.l < 30) return 'Charcoal Dust';
            if (hsl.l < 45) return 'Storm Cloud';
            if (hsl.l < 60) return 'Silver Mist';
            if (hsl.l < 75) return 'Pearl Grey';
            if (hsl.l < 90) return 'Cloud White';
            return 'Snow White';
        }

        // Get categories
        const hueCategory = this.getHueCategory(hsl.h);
        const satCategory = this.getSaturationCategory(hsl.s);
        const lightCategory = this.getLightnessCategory(hsl.l);

        // Get base name
        const baseNames = this.hueNames[hueCategory];
        const baseName = baseNames[Math.floor(Math.random() * baseNames.length)];

        // Get modifiers
        const satModifiers = this.saturationModifiers[satCategory];
        const lightModifiers = this.lightnessModifiers[lightCategory];

        const satMod = satModifiers[Math.floor(Math.random() * satModifiers.length)];
        const lightMod = lightModifiers[Math.floor(Math.random() * lightModifiers.length)];

        // Combine modifiers with base name
        const parts = [lightMod, satMod, baseName].filter(p => p !== '');

        return this.capitalizeWords(parts.join(' '));
    }

    /**
     * Capitalize first letter of each word
     */
    capitalizeWords(str) {
        return str.split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    /**
     * Convert RGB to HSL
     */
    rgbToHsl(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }

        return { h: h * 360, s: s * 100, l: l * 100 };
    }
}
