/**
 * Color Extractor - Extract dominant colors from images
 * Creates subtle, tasteful gradients
 */

class ColorExtractor {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
    }

    /**
     * Extract color palette from an image
     * @param {HTMLImageElement} img - The image element
     * @param {number} sampleSize - Number of pixels to sample
     * @returns {Promise<Array>} Array of RGB colors
     */
    async extractColors(img, sampleSize = 100) {
        return new Promise((resolve, reject) => {
            // Wait for image to load if not already loaded
            if (!img.complete) {
                img.onload = () => this.processImage(img, sampleSize, resolve);
                img.onerror = reject;
            } else {
                this.processImage(img, sampleSize, resolve);
            }
        });
    }

    processImage(img, sampleSize, resolve) {
        // Resize canvas to small size for performance
        const maxSize = 100;
        const ratio = Math.min(maxSize / img.width, maxSize / img.height);
        this.canvas.width = img.width * ratio;
        this.canvas.height = img.height * ratio;

        // Draw image
        this.ctx.drawImage(img, 0, 0, this.canvas.width, this.canvas.height);

        // Get image data
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const pixels = imageData.data;

        // Sample colors
        const colors = [];
        const step = Math.floor(pixels.length / (sampleSize * 4));

        for (let i = 0; i < pixels.length; i += step * 4) {
            const r = pixels[i];
            const g = pixels[i + 1];
            const b = pixels[i + 2];
            const a = pixels[i + 3];

            // Skip transparent and very dark/light pixels
            if (a > 200 && !this.isExtremeColor(r, g, b)) {
                colors.push({ r, g, b });
            }
        }

        // Get dominant colors using simple clustering
        const palette = this.getDominantColors(colors, 3);
        resolve(palette);
    }

    /**
     * Check if color is too extreme (too dark or too light)
     */
    isExtremeColor(r, g, b) {
        const brightness = (r + g + b) / 3;
        return brightness < 20 || brightness > 235;
    }

    /**
     * Get dominant colors using k-means clustering
     */
    getDominantColors(colors, k = 3) {
        if (colors.length === 0) {
            return [{ r: 52, g: 52, b: 52 }]; // Fallback to dark gray
        }

        // Simple k-means clustering
        let centroids = this.initializeCentroids(colors, k);

        for (let iteration = 0; iteration < 10; iteration++) {
            const clusters = Array(k).fill(null).map(() => []);

            // Assign colors to nearest centroid
            colors.forEach(color => {
                let minDist = Infinity;
                let clusterIndex = 0;

                centroids.forEach((centroid, i) => {
                    const dist = this.colorDistance(color, centroid);
                    if (dist < minDist) {
                        minDist = dist;
                        clusterIndex = i;
                    }
                });

                clusters[clusterIndex].push(color);
            });

            // Update centroids
            centroids = clusters.map(cluster => {
                if (cluster.length === 0) return centroids[0];

                const sum = cluster.reduce((acc, c) => ({
                    r: acc.r + c.r,
                    g: acc.g + c.g,
                    b: acc.b + c.b
                }), { r: 0, g: 0, b: 0 });

                return {
                    r: Math.round(sum.r / cluster.length),
                    g: Math.round(sum.g / cluster.length),
                    b: Math.round(sum.b / cluster.length)
                };
            });
        }

        return centroids;
    }

    initializeCentroids(colors, k) {
        const centroids = [];
        const step = Math.floor(colors.length / k);

        for (let i = 0; i < k; i++) {
            const index = Math.min(i * step, colors.length - 1);
            centroids.push({ ...colors[index] });
        }

        return centroids;
    }

    colorDistance(c1, c2) {
        return Math.sqrt(
            Math.pow(c1.r - c2.r, 2) +
            Math.pow(c1.g - c2.g, 2) +
            Math.pow(c1.b - c2.b, 2)
        );
    }

    /**
     * Create a tasteful gradient from palette
     * Makes colors more muted and darker for background use
     */
    createGradient(palette) {
        if (!palette || palette.length === 0) {
            return 'radial-gradient(circle at center, #343434, #1a1a1a)';
        }

        // Darken and desaturate colors for subtle effect
        const subtleColors = palette.map(color => {
            const hsl = this.rgbToHsl(color.r, color.g, color.b);

            // Reduce saturation and lightness for subtlety
            hsl.s = Math.min(hsl.s * 0.7, 50); // Max 50% saturation (more intensive)
            hsl.l = Math.min(hsl.l * 0.5, 35); // Max 35% lightness (brighter)

            return this.hslToRgb(hsl.h, hsl.s, hsl.l);
        });

        // Create radial gradient with multiple color stops
        const color1 = subtleColors[0] || { r: 52, g: 52, b: 52 };
        const color2 = subtleColors[1] || { r: 26, g: 26, b: 26 };
        const color3 = subtleColors[2] || { r: 20, g: 20, b: 20 };

        return `radial-gradient(circle at center,
            rgb(${color1.r}, ${color1.g}, ${color1.b}) 0%,
            rgb(${color2.r}, ${color2.g}, ${color2.b}) 50%,
            rgb(${color3.r}, ${color3.g}, ${color3.b}) 100%)`;
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

    /**
     * Convert HSL to RGB
     */
    hslToRgb(h, s, l) {
        h /= 360;
        s /= 100;
        l /= 100;

        let r, g, b;

        if (s === 0) {
            r = g = b = l;
        } else {
            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };

            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;

            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }

        return {
            r: Math.round(r * 255),
            g: Math.round(g * 255),
            b: Math.round(b * 255)
        };
    }
}
