/**
 * Logo Ripple Effect
 * Uses PixiJS displacement filter for water ripple on logo and tagline
 */

document.addEventListener('DOMContentLoaded', function() {
    const logoElement = document.querySelector('.blob-text');
    const taglineElement = document.querySelector('.tagline');
    const taglineTextEl = document.querySelector('.tagline-text');
    const taglineDotsEl = document.querySelector('.tagline-dots');

    if (!logoElement || typeof PIXI === 'undefined') return;

    // Wait for Britanica font to load before rendering
    document.fonts.ready.then(() => {
        // Check if Britanica is loaded, if not wait a bit more
        const checkFont = () => {
            if (document.fonts.check('800 48px Britanica')) {
                initPixiLogo();
            } else {
                setTimeout(checkFont, 100);
            }
        };
        checkFont();
    });

    function initPixiLogo() {

    // Get container for positioning
    const logoContainer = document.querySelector('.logo-container');
    const containerRect = logoContainer.getBoundingClientRect();

    // Create PixiJS application sized to cover logo container
    const app = new PIXI.Application({
        width: containerRect.width + 200,
        height: containerRect.height + 200,
        backgroundAlpha: 0,
        antialias: true,
        resolution: window.devicePixelRatio || 1,
        autoDensity: true
    });

    // Position canvas over container
    const canvas = app.view;
    canvas.style.position = 'absolute';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = '3';
    canvas.style.left = '-100px';
    canvas.style.top = '-100px';

    // Insert canvas
    logoContainer.style.position = 'relative';
    logoContainer.insertBefore(canvas, logoElement);

    // Hide original elements
    logoElement.style.visibility = 'hidden';
    if (taglineElement) taglineElement.style.visibility = 'hidden';

    // Create displacement sprite from procedural noise
    const displacementSprite = createDisplacementSprite(app);
    displacementSprite.texture.baseTexture.wrapMode = PIXI.WRAP_MODES.REPEAT;
    displacementSprite.renderable = false;
    app.stage.addChild(displacementSprite);

    // Create displacement filter
    const DisplacementFilter = PIXI.filters?.DisplacementFilter || PIXI.DisplacementFilter;
    const displacementFilter = new DisplacementFilter(displacementSprite);
    displacementFilter.scale.x = 0;
    displacementFilter.scale.y = 0;

    // No shockwave - just persistent displacement ripple

    // Get computed styles
    const logoStyle = window.getComputedStyle(logoElement);
    const logoFontSize = parseFloat(logoStyle.fontSize);

    // Create logo text with exact styling
    const logoText = new PIXI.Text('crbntyp', {
        fontFamily: 'Britanica, Arial',
        fontSize: logoFontSize,
        fontWeight: '800',
        fill: 0x1b1b1b,
        align: 'center',
        letterSpacing: 0
    });

    // Position logo text
    logoText.anchor.set(0.5, 0.5);
    logoText.x = app.screen.width / 2;
    logoText.y = 100 + logoFontSize / 2;

    // Apply displacement filter only
    logoText.filters = [displacementFilter];
    app.stage.addChild(logoText);

    // Create mask for logo (clip bottom 40%, show top 60%)
    const logoMask = new PIXI.Graphics();
    logoMask.beginFill(0xffffff);
    const logoBounds = logoText.getBounds();
    const clipFromBottom = 0.40;
    const visibleHeight = logoBounds.height * (1 - clipFromBottom);
    const logoTop = logoText.y - logoBounds.height / 2;
    logoMask.drawRect(0, 0, app.screen.width, logoTop + visibleHeight);
    logoMask.endFill();
    app.stage.addChild(logoMask);
    logoText.mask = logoMask;

    // Create tagline text
    let taglineText = null;
    if (taglineElement) {
        const taglineStyle = window.getComputedStyle(taglineElement);
        const taglineFontSize = parseFloat(taglineStyle.fontSize);

        taglineText = new PIXI.Text('', {
            fontFamily: 'Britanica, Arial',
            fontSize: taglineFontSize,
            fontWeight: '400',
            fill: 0x1b1b1b,
            align: 'right',
            letterSpacing: taglineFontSize * 0.02
        });

        // Position tagline (right-aligned, below logo)
        taglineText.anchor.set(1, 0);
        taglineText.x = logoText.x + logoBounds.width / 2;
        taglineText.y = logoText.y + logoBounds.height * 0.56 - taglineFontSize * 2.5;

        // Apply same filter as logo
        taglineText.filters = [displacementFilter];
        app.stage.addChild(taglineText);

        // Watch for tagline text changes
        function updateTaglineText() {
            const mainText = taglineTextEl ? taglineTextEl.textContent : '';
            const dots = taglineDotsEl ? taglineDotsEl.textContent : '';
            taglineText.text = mainText + dots;
        }

        // Initial update
        updateTaglineText();

        // Observe changes
        if (taglineTextEl) {
            const observer = new MutationObserver(updateTaglineText);
            observer.observe(taglineTextEl, { childList: true, characterData: true, subtree: true });
        }
        if (taglineDotsEl) {
            const observer = new MutationObserver(updateTaglineText);
            observer.observe(taglineDotsEl, { childList: true, characterData: true, subtree: true });
        }
    }

    // Ripple animation state
    let rippleTime = 0;
    let pulseIntensity = 0;
    let baseRipple = 3; // Persistent subtle base ripple
    let taglineFadingOut = false;

    // Expose pulse trigger - syncs with blob pulses
    window.triggerLogoRipple = function(intensity) {
        // Add to current intensity for chain effect
        pulseIntensity += intensity * 12;
    };

    // Expose tagline fade trigger
    window.fadeTagline = function() {
        taglineFadingOut = true;
    };

    // Watch for tagline fade (triggered by typewriter.js)
    if (taglineElement && taglineText) {
        const taglineObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'style') {
                    const opacity = parseFloat(taglineElement.style.opacity);
                    if (opacity === 0 || taglineElement.style.opacity === '0') {
                        taglineFadingOut = true;
                    }
                }
            });
        });
        taglineObserver.observe(taglineElement, { attributes: true, attributeFilter: ['style'] });
    }

    // Animation loop
    app.ticker.add((delta) => {
        rippleTime += delta * 0.02; // Slower ripple movement

        // Move displacement sprite - very subtle movement
        displacementSprite.x = Math.sin(rippleTime * 0.3) * 5;
        displacementSprite.y = Math.cos(rippleTime * 0.2) * 5;

        // Combine base ripple with pulse
        const targetScale = baseRipple + pulseIntensity;

        // Smooth transition
        displacementFilter.scale.x += (targetScale - displacementFilter.scale.x) * 0.15;
        displacementFilter.scale.y += (targetScale - displacementFilter.scale.y) * 0.15;

        // Decay pulse
        pulseIntensity *= 0.94;

        // Fade out tagline if triggered
        if (taglineFadingOut && taglineText) {
            taglineText.alpha -= 0.05; // Faster fade
            if (taglineText.alpha <= 0) {
                taglineText.alpha = 0;
                taglineText.visible = false;
            }
        }
    });

    // Handle resize
    window.addEventListener('resize', debounce(function() {
        const logoStyle = window.getComputedStyle(logoElement);
        const newFontSize = parseFloat(logoStyle.fontSize);
        logoText.style.fontSize = newFontSize;

        if (taglineText && taglineElement) {
            const taglineStyle = window.getComputedStyle(taglineElement);
            taglineText.style.fontSize = parseFloat(taglineStyle.fontSize);
        }
    }, 100));

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    } // end initPixiLogo
});

/**
 * Create procedural displacement texture
 */
function createDisplacementSprite(app) {
    const size = 512;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');

    const imageData = ctx.createImageData(size, size);
    const data = imageData.data;

    for (let y = 0; y < size; y++) {
        for (let x = 0; x < size; x++) {
            const i = (y * size + x) * 4;

            const noise =
                Math.sin(x * 0.02) * 50 +
                Math.sin(y * 0.02) * 50 +
                Math.sin((x + y) * 0.01) * 30 +
                Math.sin(Math.sqrt(x * x + y * y) * 0.03) * 40 +
                128;

            const value = Math.max(0, Math.min(255, noise));

            data[i] = value;
            data[i + 1] = value;
            data[i + 2] = value;
            data[i + 3] = 255;
        }
    }

    ctx.putImageData(imageData, 0, 0);

    const texture = PIXI.Texture.from(canvas);
    const sprite = new PIXI.Sprite(texture);

    sprite.width = app.screen.width;
    sprite.height = app.screen.height;

    return sprite;
}
