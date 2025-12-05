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

    // Pulse chain settings
    let lastChainTime = 0;
    let lastCoreOpacity = 0;
    const CHAIN_COOLDOWN = 8000; // Minimum ms between pulse chains (8 seconds)
    const alienSounds = ['/sounds/alien-random-1.mp3', '/sounds/alien-random-2.mp3', '/sounds/alien-random-4.mp3'];

    // Visual pulse state
    let pulseScale = 0;
    let pulseDecay = 0.85; // Even slower decay for more impactful pulse

    function playPulseSound(intensity) {
        const soundFile = alienSounds[Math.floor(Math.random() * alienSounds.length)];
        const audio = new Audio(soundFile);
        audio.volume = 0.12 + intensity * 0.08;
        audio.play().catch(() => {});
    }

    // Trigger a chain of 1-5 pulses
    function triggerPulseChain() {
        const pulseCount = 1 + Math.floor(Math.random() * 5); // 1-5 pulses
        const pulseDelay = 50 + Math.random() * 100; // 50-150ms between pulses (quick chaining)

        // Play ONE random sound for this chain
        const chainIntensity = 0.5 + Math.random() * 0.5;
        playPulseSound(chainIntensity);

        for (let i = 0; i < pulseCount; i++) {
            setTimeout(() => {
                const intensity = 0.5 + Math.random() * 0.5; // 0.5-1.0

                // Visual pulse - very big and impactful
                pulseScale = intensity * 0.7;

                // Logo ripple
                if (window.triggerLogoRipple) {
                    window.triggerLogoRipple(intensity);
                }
            }, i * pulseDelay);
        }
    }

    // Intensify blob colors - called when animation finishes
    let isIntensified = false;
    window.intensifyBlob = function() {
        isIntensified = true;
    };

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
        const pScale = 1 + Math.sin(elapsed * 0.5) * 0.05 + Math.sin(elapsed * 0.23) * 0.04 + pulseScale;
        const pTransX = Math.sin(elapsed * 0.4) * 8 + Math.sin(elapsed * 0.17) * 5;
        const pTransY = Math.cos(elapsed * 0.35) * 8 + Math.cos(elapsed * 0.21) * 5;
        // Continuous rotation - never resets
        const pRotate = elapsed * 8 + Math.sin(elapsed * 0.3) * 15;

        // Intensity pulsing - opacity swells (boost when intensified)
        const intensityBoost = isIntensified ? 0.35 : 0;
        const pOpacity = Math.min(1, 0.8 + intensityBoost + Math.sin(elapsed * 0.15) * 0.15 + Math.sin(elapsed * 0.07) * 0.1);
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
        const sScale = 0.95 + Math.sin(elapsed * 0.4 + 1) * 0.06 + Math.sin(elapsed * 0.19) * 0.04 + pulseScale * 0.8;
        const sTransX = Math.sin(elapsed * 0.3 + 2) * 10 + Math.sin(elapsed * 0.13) * 6;
        const sTransY = Math.cos(elapsed * 0.28 + 1) * 10 + Math.cos(elapsed * 0.16) * 6;
        // Counter-rotation for visual interest
        const sRotate = -elapsed * 6 + Math.sin(elapsed * 0.25 + 1) * 20;

        // Intensity pulsing - offset phase from primary for interesting overlap
        const sOpacity = Math.min(1, 0.75 + intensityBoost + Math.sin(elapsed * 0.12 + 2) * 0.2 + Math.sin(elapsed * 0.05 + 1) * 0.1);
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
        const tScale = 1 + Math.sin(elapsed * 0.3) * 0.07 + Math.sin(elapsed * 0.11) * 0.05 + pulseScale * 0.6;
        const tTransX = Math.sin(elapsed * 0.25 + 3) * 12 + Math.sin(elapsed * 0.09) * 7;
        const tTransY = Math.cos(elapsed * 0.22 + 2) * 12 + Math.cos(elapsed * 0.12) * 7;
        // Slower rotation with wobble
        const tRotate = elapsed * 4 + Math.sin(elapsed * 0.18) * 25;

        // Orange flares up occasionally - longer cycle
        const tOpacity = Math.min(1, 0.6 + intensityBoost + Math.sin(elapsed * 0.08) * 0.25 + Math.sin(elapsed * 0.03) * 0.15);
        const tBlur = 40 - Math.sin(elapsed * 0.08) * 10 - Math.sin(elapsed * 0.04) * 5;

        tertiary.style.borderRadius = generateBorderRadius(t1);
        tertiary.style.transform = `scale(${tScale}) translate(${tTransX}%, ${tTransY}%) rotate(${tRotate}deg)`;
        tertiary.style.opacity = tOpacity;
        tertiary.style.filter = `blur(${tBlur}px)`;

        // CORE - white glow (gentle pulse, gets bright when colors intensify)
        const baseCoreScale = 1 + Math.sin(elapsed * 0.6) * 0.08 + Math.sin(elapsed * 0.27) * 0.05;
        const coreScale = baseCoreScale + pulseScale; // Add pulse effect
        // Core brightens when other blobs are intense
        const coreOpacity = 0.6 + intensityBoost * 0.5 + Math.sin(elapsed * 0.5) * 0.2 + Math.sin(elapsed * 0.31) * 0.15 + Math.sin(elapsed * 0.11) * 0.1;
        const coreBlur = 30 - Math.sin(elapsed * 0.5) * 8 - Math.sin(elapsed * 0.19) * 5;

        core.style.transform = `scale(${coreScale})`;
        core.style.opacity = coreOpacity;
        core.style.filter = `blur(${coreBlur}px)`;

        // Decay pulse scale
        pulseScale *= pulseDecay;

        // Detect peaks and trigger pulse chains
        const now = Date.now();
        if (coreOpacity < lastCoreOpacity && lastCoreOpacity > 0.65 && now - lastChainTime > CHAIN_COOLDOWN) {
            triggerPulseChain();
            lastChainTime = now;
        }
        lastCoreOpacity = coreOpacity;

        requestAnimationFrame(animate);
    }

    animate();
});

