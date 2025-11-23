/**
 * Cinders Effect
 * Sparks that explode from center and float around
 */

document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('cinders-container');
  const cinders = document.querySelectorAll('.cinder');

  if (!container || cinders.length === 0) return;

  const colors = {
    white: '#ffffff',
    cyan: '#00ffff'
  };

  // Initialize cinders at center, hidden
  cinders.forEach(cinder => {
    cinder.style.opacity = '0';
    cinder.style.left = '50%';
    cinder.style.top = '50%';

    // Set color based on data attribute
    const colorName = cinder.dataset.color;
    const spark = cinder.querySelector('.cinder-spark');
    if (spark && colors[colorName]) {
      spark.style.backgroundColor = colors[colorName];
    }
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

        // After explosion, start floating
        setTimeout(() => {
          startFloating(cinder, targetX, targetY);
        }, 1200);
      }, index * 150);
    });
  };

  // Float animation for individual cinder
  function startFloating(cinder, startX, startY) {
    const floatSpeed = 0.0003 + Math.random() * 0.0003;
    const floatRadius = 20 + Math.random() * 30;
    const phaseX = Math.random() * Math.PI * 2;
    const phaseY = Math.random() * Math.PI * 2;
    let animStartTime = Date.now();

    cinder.style.transition = 'none';

    function float() {
      const elapsed = Date.now() - animStartTime;
      const offsetX = Math.sin(elapsed * floatSpeed + phaseX) * floatRadius / window.innerWidth * 100;
      const offsetY = Math.cos(elapsed * floatSpeed * 0.7 + phaseY) * floatRadius / window.innerHeight * 100;

      // Keep within bounds
      let newX = startX + offsetX;
      let newY = startY + offsetY;
      newX = Math.max(5, Math.min(95, newX));
      newY = Math.max(5, Math.min(95, newY));

      cinder.style.left = newX + '%';
      cinder.style.top = newY + '%';

      requestAnimationFrame(float);
    }

    float();
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
