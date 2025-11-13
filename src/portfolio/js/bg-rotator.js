/**
 * Background Image Rotator
 * Smoothly cycles through background images with crossfade effect
 */

document.addEventListener('DOMContentLoaded', function() {
    const bgContainer = document.getElementById('bgRotator');
    if (!bgContainer) return;

    const images = [
        '/img/bg1.jpg',
        '/img/bg2.jpg',
        '/img/bg3.jpg',
        '/img/bg4.jpg',
        '/img/bg5.jpg'
    ];

    let currentIndex = 0;
    let bgLayer1, bgLayer2;
    let activeLayer = 1;

    function init() {
        // Create two background layers for crossfading
        bgLayer1 = document.createElement('div');
        bgLayer1.className = 'bg-layer';
        bgLayer1.style.backgroundImage = `url('${images[0]}')`;
        bgLayer1.style.opacity = '1';

        bgLayer2 = document.createElement('div');
        bgLayer2.className = 'bg-layer';
        bgLayer2.style.opacity = '0';

        bgContainer.appendChild(bgLayer1);
        bgContainer.appendChild(bgLayer2);

        // Start rotation after 5 seconds
        setTimeout(rotateBackground, 5000);
    }

    function rotateBackground() {
        // Move to next image
        currentIndex = (currentIndex + 1) % images.length;

        // Determine which layer to update
        if (activeLayer === 1) {
            // Update layer 2 with new image
            bgLayer2.style.backgroundImage = `url('${images[currentIndex]}')`;
            bgLayer2.style.opacity = '1';
            bgLayer1.style.opacity = '0';
            activeLayer = 2;
        } else {
            // Update layer 1 with new image
            bgLayer1.style.backgroundImage = `url('${images[currentIndex]}')`;
            bgLayer1.style.opacity = '1';
            bgLayer2.style.opacity = '0';
            activeLayer = 1;
        }

        // Continue rotation
        setTimeout(rotateBackground, 5000);
    }

    init();
});
