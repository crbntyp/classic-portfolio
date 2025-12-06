/**
 * Cinders Effect
 * Sparks that explode from center and float around
 */

document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('cinders-container');
  const cinders = document.querySelectorAll('.cinder');

  if (!container || cinders.length === 0) return;

  // Colors now handled via CSS using data-color attribute
  const colors = {
    white: true,
    cyan: true
  };

  // Audio context for blip sounds
  let audioContext = null;

  function playBlip() {
    // Initialize audio context on first interaction (browser requirement)
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }

    // Resume context if suspended (browser autoplay policy)
    if (audioContext.state === 'suspended') {
      audioContext.resume();
    }

    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Soft quick blip
    oscillator.frequency.setValueAtTime(500, audioContext.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(400, audioContext.currentTime + 0.02);
    oscillator.type = 'triangle';

    // Very quiet, instant fade
    gainNode.gain.setValueAtTime(0.015, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.02);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.02);
  }

  // Initialize cinders at center, hidden
  cinders.forEach(cinder => {
    cinder.style.opacity = '0';
    cinder.style.left = '50%';
    cinder.style.top = '50%';

    // Play blip on hover
    cinder.addEventListener('mouseenter', playBlip);
  });

  // Explode cinders from center to random positions across screen
  window.explodeCinders = function() {
    cinders.forEach((cinder, index) => {
      // Random position across the entire screen
      const targetX = 10 + Math.random() * 80; // 10-90% of screen width
      const targetY = 10 + Math.random() * 80; // 10-90% of screen height

      // Staggered animation
      setTimeout(() => {
        cinder.style.transition = 'all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        cinder.style.opacity = '1';
        cinder.style.left = targetX + '%';
        cinder.style.top = targetY + '%';

        // After explosion, start orbiting around center
        setTimeout(() => {
          startOrbiting(cinder, targetX, targetY, index);
        }, 1200);
      }, index * 150);
    });
  };

  // Orbit animation - side view (elliptical with depth effect)
  function startOrbiting(cinder, startX, startY, index) {
    const spark = cinder.querySelector('.cinder-spark');

    // Center of orbit (screen center)
    const centerX = 50;
    const centerY = 50;

    // Calculate initial angle and orbit radius from explosion position
    const dx = startX - centerX;
    const dy = startY - centerY;
    const initialAngle = Math.atan2(dy, dx);
    const orbitRadiusX = Math.sqrt(dx * dx + dy * dy); // Horizontal radius
    const orbitRadiusY = orbitRadiusX * 0.3; // Vertical radius (flattened for side view)

    // Orbit speed - varies per cinder (slower)
    const orbitSpeed = 0.00004 + Math.random() * 0.00003;
    // Direction: some orbit clockwise, some counter-clockwise
    const direction = Math.random() > 0.5 ? 1 : -1;

    let animStartTime = Date.now();
    let isHovered = false;
    let pausedTime = 0;
    let totalPausedDuration = 0;

    cinder.style.transition = 'none';

    // Pause on hover
    cinder.addEventListener('mouseenter', () => {
      isHovered = true;
      pausedTime = Date.now();
    });

    cinder.addEventListener('mouseleave', () => {
      totalPausedDuration += Date.now() - pausedTime;
      isHovered = false;
    });

    function orbit() {
      if (!isHovered) {
        const elapsed = Date.now() - animStartTime - totalPausedDuration;

        // Current angle in orbit
        const currentAngle = initialAngle + elapsed * orbitSpeed * direction;

        // Elliptical orbit (side view - wide horizontally, narrow vertically)
        const newX = centerX + Math.cos(currentAngle) * orbitRadiusX;
        const newY = centerY + Math.sin(currentAngle) * orbitRadiusY;

        // Depth effect based on position in orbit
        // When sin(angle) is positive, cinder is "in front" (larger, brighter)
        // When sin(angle) is negative, cinder is "behind" (smaller, faded - behind blob)
        const depth = Math.sin(currentAngle);
        const scale = 0.3 + (depth + 1) * 0.85; // Scale from 0.3 (back) to 2.0 (front)
        const opacity = 0.1 + (depth + 1) * 0.45; // Opacity from 0.1 (back/hidden) to 1.0 (front)

        // Keep within screen bounds
        const clampedX = Math.max(5, Math.min(95, newX));
        const clampedY = Math.max(5, Math.min(95, newY));

        cinder.style.left = clampedX + '%';
        cinder.style.top = clampedY + '%';

        if (spark) {
          spark.style.transform = `scale(${scale})`;
          spark.style.opacity = opacity;
        }
      }

      requestAnimationFrame(orbit);
    }

    orbit();
  }

  // Reset cinders to center (for loop)
  window.resetCinders = function() {
    cinders.forEach(cinder => {
      cinder.style.transition = 'opacity 0.5s ease';
      cinder.style.opacity = '0';

      setTimeout(() => {
        cinder.style.transition = 'none';
        cinder.style.left = '50%';
        cinder.style.top = '50%';
      }, 500);
    });
  };

  // Double-tap behavior for touch devices
  // First tap shows tooltip, second tap navigates
  let lastTappedCinder = null;
  let tapTimeout = null;

  function isTouchDevice() {
    return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
  }

  if (isTouchDevice()) {
    cinders.forEach(cinder => {
      cinder.addEventListener('click', function(e) {
        // If this is the same cinder tapped before, allow navigation
        if (lastTappedCinder === cinder) {
          // Clear state and let the click through (navigation happens)
          lastTappedCinder = null;
          clearTimeout(tapTimeout);
          return; // Allow default behavior
        }

        // First tap - prevent navigation, show tooltip
        e.preventDefault();

        // Remove active state from previous cinder
        if (lastTappedCinder) {
          lastTappedCinder.classList.remove('tooltip-active');
        }

        // Set this as the active cinder
        lastTappedCinder = cinder;
        cinder.classList.add('tooltip-active');

        // Reset after 3 seconds of inactivity
        clearTimeout(tapTimeout);
        tapTimeout = setTimeout(() => {
          if (lastTappedCinder) {
            lastTappedCinder.classList.remove('tooltip-active');
          }
          lastTappedCinder = null;
        }, 3000);
      });
    });

    // Clear state when tapping elsewhere
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.cinder')) {
        if (lastTappedCinder) {
          lastTappedCinder.classList.remove('tooltip-active');
        }
        lastTappedCinder = null;
        clearTimeout(tapTimeout);
      }
    });
  }
});
