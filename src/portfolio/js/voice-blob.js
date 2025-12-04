/**
 * Voice Blob Animation
 * Uses sine waves for truly seamless organic movement - no keyframes, no snapping
 */

document.addEventListener('DOMContentLoaded', function() {
    const blobContainer = document.getElementById('voice-blob');
    if (!blobContainer) return;

    const blobs = blobContainer.querySelectorAll('.voice-blob');
    if (blobs.length < 4) return;

    const primary = blobs[0];
    const secondary = blobs[1];
    const tertiary = blobs[2];
    const core = blobs[3];

    // Disable CSS animations - we're doing it in JS
    blobs.forEach(blob => {
        blob.style.animation = 'none';
    });

    // Mouse tracking
    let mouseX = 0;
    let mouseY = 0;
    let currentMouseX = 0;
    let currentMouseY = 0;

    document.addEventListener('mousemove', function(e) {
        mouseX = (e.clientX - window.innerWidth / 2) / (window.innerWidth / 2);
        mouseY = (e.clientY - window.innerHeight / 2) / (window.innerHeight / 2);
    });

    // Generate border-radius from 8 values
    function generateBorderRadius(values) {
        return `${values[0]}% ${values[1]}% ${values[2]}% ${values[3]}% / ${values[4]}% ${values[5]}% ${values[6]}% ${values[7]}%`;
    }

    // Organic morphing using multiple sine waves
    function morphValue(time, baseValue, amplitude, speeds) {
        let value = baseValue;
        speeds.forEach((speed, i) => {
            value += Math.sin(time * speed + i * 1.5) * amplitude;
        });
        return value;
    }

    let startTime = Date.now();

    function animate() {
        const elapsed = (Date.now() - startTime) / 1000; // seconds

        // Smooth mouse following
        currentMouseX += (mouseX - currentMouseX) * 0.03;
        currentMouseY += (mouseY - currentMouseY) * 0.03;

        // Container offset based on mouse
        const containerOffsetX = currentMouseX * 25;
        const containerOffsetY = currentMouseY * 25;
        blobContainer.style.transform = `translate(calc(-50% + ${containerOffsetX}px), calc(-50% + ${containerOffsetY}px))`;

        // PRIMARY BLOB - cyan
        const p1 = [
            morphValue(elapsed, 50, 15, [0.3, 0.7, 0.13]),
            morphValue(elapsed, 50, 15, [0.4, 0.6, 0.11]),
            morphValue(elapsed, 50, 15, [0.35, 0.55, 0.17]),
            morphValue(elapsed, 50, 15, [0.45, 0.65, 0.19]),
            morphValue(elapsed, 50, 15, [0.38, 0.72, 0.14]),
            morphValue(elapsed, 50, 15, [0.42, 0.58, 0.12]),
            morphValue(elapsed, 50, 15, [0.33, 0.67, 0.16]),
            morphValue(elapsed, 50, 15, [0.47, 0.53, 0.18])
        ];
        const pScale = 1 + Math.sin(elapsed * 0.5) * 0.03 + Math.sin(elapsed * 0.23) * 0.02;
        const pTransX = Math.sin(elapsed * 0.4) * 3 + Math.sin(elapsed * 0.17) * 2;
        const pTransY = Math.cos(elapsed * 0.35) * 3 + Math.cos(elapsed * 0.21) * 2;
        // Continuous rotation - never resets
        const pRotate = elapsed * 8 + Math.sin(elapsed * 0.3) * 15;

        // Intensity pulsing - opacity swells
        const pOpacity = 0.7 + Math.sin(elapsed * 0.15) * 0.15 + Math.sin(elapsed * 0.07) * 0.1;
        // Blur variation - sharper when intense, softer when fading
        const pBlur = 60 - Math.sin(elapsed * 0.15) * 15 - Math.sin(elapsed * 0.09) * 8;

        primary.style.borderRadius = generateBorderRadius(p1);
        primary.style.transform = `scale(${pScale}) translate(${pTransX}%, ${pTransY}%) rotate(${pRotate}deg)`;
        primary.style.opacity = pOpacity;
        primary.style.filter = `blur(${pBlur}px)`;

        // SECONDARY BLOB - magenta (different speeds for variety)
        const s1 = [
            morphValue(elapsed, 50, 18, [0.25, 0.5, 0.09]),
            morphValue(elapsed, 50, 18, [0.32, 0.48, 0.11]),
            morphValue(elapsed, 50, 18, [0.28, 0.52, 0.13]),
            morphValue(elapsed, 50, 18, [0.35, 0.45, 0.07]),
            morphValue(elapsed, 50, 18, [0.22, 0.58, 0.15]),
            morphValue(elapsed, 50, 18, [0.38, 0.42, 0.08]),
            morphValue(elapsed, 50, 18, [0.3, 0.6, 0.12]),
            morphValue(elapsed, 50, 18, [0.27, 0.53, 0.1])
        ];
        const sScale = 0.95 + Math.sin(elapsed * 0.4 + 1) * 0.04 + Math.sin(elapsed * 0.19) * 0.03;
        const sTransX = Math.sin(elapsed * 0.3 + 2) * 4 + Math.sin(elapsed * 0.13) * 3;
        const sTransY = Math.cos(elapsed * 0.28 + 1) * 4 + Math.cos(elapsed * 0.16) * 3;
        // Counter-rotation for visual interest
        const sRotate = -elapsed * 6 + Math.sin(elapsed * 0.25 + 1) * 20;

        // Intensity pulsing - offset phase from primary for interesting overlap
        const sOpacity = 0.65 + Math.sin(elapsed * 0.12 + 2) * 0.2 + Math.sin(elapsed * 0.05 + 1) * 0.1;
        const sBlur = 55 - Math.sin(elapsed * 0.12 + 2) * 12 - Math.sin(elapsed * 0.08) * 6;

        secondary.style.borderRadius = generateBorderRadius(s1);
        secondary.style.transform = `scale(${sScale}) translate(${sTransX}%, ${sTransY}%) rotate(${sRotate}deg)`;
        secondary.style.opacity = sOpacity;
        secondary.style.filter = `blur(${sBlur}px)`;

        // TERTIARY BLOB - orange (slowest, most subtle)
        const t1 = [
            morphValue(elapsed, 50, 12, [0.2, 0.4, 0.08]),
            morphValue(elapsed, 50, 12, [0.22, 0.38, 0.1]),
            morphValue(elapsed, 50, 12, [0.18, 0.42, 0.09]),
            morphValue(elapsed, 50, 12, [0.24, 0.36, 0.11]),
            morphValue(elapsed, 50, 12, [0.19, 0.41, 0.07]),
            morphValue(elapsed, 50, 12, [0.23, 0.37, 0.12]),
            morphValue(elapsed, 50, 12, [0.21, 0.39, 0.08]),
            morphValue(elapsed, 50, 12, [0.17, 0.43, 0.1])
        ];
        const tScale = 1 + Math.sin(elapsed * 0.3) * 0.05 + Math.sin(elapsed * 0.11) * 0.03;
        const tTransX = Math.sin(elapsed * 0.25 + 3) * 5 + Math.sin(elapsed * 0.09) * 3;
        const tTransY = Math.cos(elapsed * 0.22 + 2) * 5 + Math.cos(elapsed * 0.12) * 3;
        // Slower rotation with wobble
        const tRotate = elapsed * 4 + Math.sin(elapsed * 0.18) * 25;

        // Orange flares up occasionally - longer cycle
        const tOpacity = 0.45 + Math.sin(elapsed * 0.08) * 0.25 + Math.sin(elapsed * 0.03) * 0.15;
        const tBlur = 40 - Math.sin(elapsed * 0.08) * 10 - Math.sin(elapsed * 0.04) * 5;

        tertiary.style.borderRadius = generateBorderRadius(t1);
        tertiary.style.transform = `scale(${tScale}) translate(${tTransX}%, ${tTransY}%) rotate(${tRotate}deg)`;
        tertiary.style.opacity = tOpacity;
        tertiary.style.filter = `blur(${tBlur}px)`;

        // CORE - white glow (gentle pulse, gets bright when colors intensify)
        const coreScale = 1 + Math.sin(elapsed * 0.6) * 0.08 + Math.sin(elapsed * 0.27) * 0.05;
        // Core brightens when other blobs are intense
        const coreOpacity = 0.5 + Math.sin(elapsed * 0.5) * 0.2 + Math.sin(elapsed * 0.31) * 0.15 + Math.sin(elapsed * 0.11) * 0.1;
        const coreBlur = 30 - Math.sin(elapsed * 0.5) * 8 - Math.sin(elapsed * 0.19) * 5;

        core.style.transform = `scale(${coreScale})`;
        core.style.opacity = coreOpacity;
        core.style.filter = `blur(${coreBlur}px)`;

        requestAnimationFrame(animate);
    }

    animate();
});
