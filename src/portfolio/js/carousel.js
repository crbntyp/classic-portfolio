/**
 * Vanilla JS Carousel
 * No jQuery, clean and modern
 */

class Carousel {
    constructor(container, options = {}) {
        this.container = container;
        this.slides = Array.from(container.querySelectorAll('.slide'));
        this.currentIndex = 0;
        this.isTransitioning = false;
        this.colorExtractor = new ColorExtractor();
        this.colorNamer = new ColorNamer();
        this.slidePalettes = new Map();

        // Options
        this.options = {
            showDots: options.showDots !== undefined ? options.showDots : true,
            showArrows: options.showArrows !== undefined ? options.showArrows : true,
            autoplay: options.autoplay || false,
            autoplayInterval: options.autoplayInterval || 5000,
            dynamicBackground: options.dynamicBackground !== undefined ? options.dynamicBackground : true
        };

        this.init();
    }

    async init() {
        // Show all slides but only first is active
        this.slides.forEach((slide, index) => {
            slide.classList.remove('active');
        });
        this.slides[0].classList.add('active');

        // Create navigation first (before async operations)
        this.createNavigation();

        // Create counter
        this.createCounter();

        // Create custom cursor
        this.createCustomCursor();

        // Wait for images to load before calculating height
        await this.waitForImages();

        // Calculate and set carousel height (after nav is created)
        this.updateCarouselHeight();

        // Setup event listeners
        this.setupKeyboardNav();
        this.setupTouchNav();
        this.setupResizeListener();

        // Extract colors from images if dynamic background enabled (async, don't block)
        if (this.options.dynamicBackground) {
            try {
                await this.extractAllColors();
                this.applyGradient(0);
            } catch (err) {
                console.warn('Could not extract colors for dynamic background:', err);
            }
        }

        // Auto-advance (optional - can remove if not wanted)
        // this.startAutoplay(5000);
    }

    /**
     * Extract color palettes from all slide images
     */
    async extractAllColors() {
        const promises = this.slides.map(async (slide, index) => {
            const img = slide.querySelector('.holder-img, img');
            if (img) {
                try {
                    const palette = await this.colorExtractor.extractColors(img);
                    this.slidePalettes.set(index, palette);
                } catch (err) {
                    console.warn(`Could not extract colors from slide ${index}:`, err);
                    this.slidePalettes.set(index, null);
                }
            }
        });

        await Promise.all(promises);
    }

    /**
     * Apply gradient background based on slide palette
     */
    applyGradient(index) {
        if (!this.options.dynamicBackground) return;

        const palette = this.slidePalettes.get(index);
        const gradient = this.colorExtractor.createGradient(palette);

        // Add smooth transition to body
        document.body.style.transition = 'background 1.5s ease-in-out';
        document.body.style.background = gradient;

        // Update color palette display
        this.updateColorPalette(palette);
    }

    /**
     * Update the color palette circles display
     */
    updateColorPalette(palette) {
        // Find all color palettes (one per slide)
        const paletteElements = document.querySelectorAll('.color-palette');
        if (!paletteElements.length || !palette) {
            console.warn('Color palette elements not found or palette is null');
            return;
        }

        console.log('Updating color palette with', palette);

        // Update all palettes (they'll show when their slide is active)
        paletteElements.forEach(paletteElement => {
            const circles = paletteElement.querySelectorAll('.color-palette__circle');

            palette.forEach((color, index) => {
                if (circles[index]) {
                    // Use brighter, more saturated colors for the display circles
                    const hsl = this.colorExtractor.rgbToHsl(color.r, color.g, color.b);
                    // Boost saturation and lightness for visibility
                    hsl.s = Math.min(hsl.s * 1.2, 80);
                    hsl.l = Math.max(Math.min(hsl.l * 1.5, 60), 40);
                    const brightColor = this.colorExtractor.hslToRgb(hsl.h, hsl.s, hsl.l);

                    circles[index].style.background = `rgb(${brightColor.r}, ${brightColor.g}, ${brightColor.b})`;
                    circles[index].style.opacity = '1';

                    // Generate creative color name and add tooltip
                    const colorName = this.colorNamer.nameColor(brightColor.r, brightColor.g, brightColor.b);
                    circles[index].setAttribute('data-tooltip', colorName);
                    circles[index].setAttribute('data-tooltip-position', 'top');
                }
            });
        });
    }

    createNavigation() {
        // Create arrows if enabled
        if (this.options.showArrows) {
            const nav = document.createElement('div');
            nav.className = 'carousel-nav';

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'carousel-btn carousel-btn-prev';
            prevBtn.innerHTML = '<i class="las la-angle-left"></i>';
            prevBtn.setAttribute('aria-label', 'Previous slide');
            prevBtn.addEventListener('click', () => this.prev());

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'carousel-btn carousel-btn-next';
            nextBtn.innerHTML = '<i class="las la-angle-right"></i>';
            nextBtn.setAttribute('aria-label', 'Next slide');
            nextBtn.addEventListener('click', () => this.next());

            nav.appendChild(prevBtn);
            nav.appendChild(nextBtn);
            this.container.appendChild(nav);

            // Store reference for height updates
            this.carouselNav = nav;
        }

        // Create dots if enabled
        if (this.options.showDots) {
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'carousel-dots';

            this.slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.className = 'carousel-dot';
                dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
                if (index === 0) dot.classList.add('active');
                dot.addEventListener('click', () => this.goTo(index));
                dotsContainer.appendChild(dot);
            });

            this.container.appendChild(dotsContainer);
            this.dotsContainer = dotsContainer;
        }
    }

    createCounter() {
        const counter = document.createElement('div');
        counter.className = 'carousel-counter';
        counter.textContent = `1/${this.slides.length}`;
        document.body.appendChild(counter);
        this.counter = counter;
    }

    createCustomCursor() {
        // Create custom cursor element
        const cursor = document.createElement('div');
        cursor.className = 'carousel-cursor';
        cursor.textContent = 'Read More';
        document.body.appendChild(cursor);
        this.customCursor = cursor;

        // Setup cursor tracking
        const holderLinks = this.container.querySelectorAll('.holder a');

        holderLinks.forEach(link => {
            // Show cursor on hover
            link.addEventListener('mouseenter', () => {
                this.customCursor.classList.add('active');
            });

            // Hide cursor on leave
            link.addEventListener('mouseleave', () => {
                this.customCursor.classList.remove('active');
            });

            // Track mouse movement
            link.addEventListener('mousemove', (e) => {
                this.customCursor.style.left = e.clientX + 'px';
                this.customCursor.style.top = e.clientY + 'px';
            });
        });
    }

    updateCounter() {
        if (this.counter) {
            this.counter.textContent = `${this.currentIndex + 1}/${this.slides.length}`;
        }
    }

    setupKeyboardNav() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.prev();
            } else if (e.key === 'ArrowRight') {
                this.next();
            }
        });
    }

    setupTouchNav() {
        let touchStartX = 0;
        let touchEndX = 0;

        this.container.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        this.container.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }, { passive: true });

        this.handleSwipe = () => {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        };
    }

    waitForImages() {
        const images = this.container.querySelectorAll('img');
        const promises = Array.from(images).map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(resolve => {
                img.addEventListener('load', resolve);
                img.addEventListener('error', resolve);
            });
        });
        return Promise.all(promises);
    }

    setupResizeListener() {
        let resizeFrame;
        window.addEventListener('resize', () => {
            if (resizeFrame) {
                cancelAnimationFrame(resizeFrame);
            }
            resizeFrame = requestAnimationFrame(() => {
                this.updateCarouselHeight();
            });
        });
    }

    updateCarouselHeight() {
        let maxHeight = 0;
        const containerWidth = this.container.offsetWidth;

        // Temporarily remove inline height to get accurate measurements
        const originalHeight = this.container.style.height;
        this.container.style.height = 'auto';

        this.slides.forEach((slide) => {
            // Clone the slide to measure it without affecting the original
            const clone = slide.cloneNode(true);
            clone.style.position = 'relative';
            clone.style.visibility = 'hidden';
            clone.style.opacity = '1';
            clone.style.display = 'flex';
            clone.style.width = containerWidth + 'px';
            clone.style.maxWidth = containerWidth + 'px';

            // Make sure images scale properly
            const images = clone.querySelectorAll('img');
            images.forEach(img => {
                img.style.width = '100%';
                img.style.height = 'auto';
            });

            // Make all absolutely positioned children relative for proper measurement
            const projectHeading = clone.querySelector('.project-heading');
            const colorPalette = clone.querySelector('.color-palette');

            if (projectHeading) {
                projectHeading.style.position = 'relative';
                projectHeading.style.bottom = 'auto';
            }

            if (colorPalette) {
                colorPalette.style.position = 'relative';
                colorPalette.style.bottom = 'auto';
                colorPalette.style.left = 'auto';
                colorPalette.style.transform = 'none';
            }

            // Add to DOM temporarily (append to body for accurate measurement)
            document.body.appendChild(clone);

            // Force reflow
            clone.offsetHeight;

            // Measure
            const slideHeight = clone.offsetHeight;
            if (slideHeight > maxHeight) {
                maxHeight = slideHeight;
            }

            // Remove clone
            document.body.removeChild(clone);
        });

        // Set the carousel height to the tallest slide
        if (maxHeight > 0) {
            this.container.style.height = maxHeight + 'px';

            // Also set the same height on carousel-nav for arrow alignment
            if (this.carouselNav) {
                this.carouselNav.style.height = maxHeight + 'px';
            }

            console.log('Carousel height updated to:', maxHeight + 'px', 'Container width:', containerWidth + 'px');
        } else {
            this.container.style.height = originalHeight;
        }
    }

    goTo(index) {
        if (this.isTransitioning || index === this.currentIndex) return;
        if (index < 0 || index >= this.slides.length) return;

        this.isTransitioning = true;

        const currentSlide = this.slides[this.currentIndex];
        const nextSlide = this.slides[index];

        // Remove active from current, add to next
        // CSS handles the opacity transition
        currentSlide.classList.remove('active');
        nextSlide.classList.add('active');

        // Apply gradient immediately when transition starts
        this.applyGradient(index);

        // Update dots if they exist
        if (this.dotsContainer) {
            const dots = this.dotsContainer.querySelectorAll('.carousel-dot');
            dots[this.currentIndex].classList.remove('active');
            dots[index].classList.add('active');
        }

        this.currentIndex = index;

        // Update counter
        this.updateCounter();

        // Wait for CSS transition to complete
        setTimeout(() => {
            this.isTransitioning = false;
        }, 500);
    }

    next() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.goTo(nextIndex);
    }

    prev() {
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.goTo(prevIndex);
    }

    startAutoplay(interval = 5000) {
        this.autoplayInterval = setInterval(() => {
            this.next();
        }, interval);

        // Pause on hover
        this.container.addEventListener('mouseenter', () => {
            clearInterval(this.autoplayInterval);
        });

        this.container.addEventListener('mouseleave', () => {
            this.startAutoplay(interval);
        });
    }

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const carouselElement = document.querySelector('.carousel');
    if (carouselElement) {
        window.portfolioCarousel = new Carousel(carouselElement, {
            showDots: false,
            showArrows: true
        });
    }
});
