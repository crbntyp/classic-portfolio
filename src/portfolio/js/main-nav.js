/**
 * Main Navigation Toggle
 * Handles primary navigation with rocket icon and random positioning animation
 */

document.addEventListener('DOMContentLoaded', function() {
  const trigger = document.querySelector('.main-nav__trigger');
  const popup = document.querySelector('.main-nav__popup');
  const items = document.querySelectorAll('.main-nav__item');

  console.log('Menu initialized', { trigger, popup });

  function setRandomPositions() {
    const triggerRect = trigger.getBoundingClientRect();
    const startX = triggerRect.left + triggerRect.width / 2;
    const startY = triggerRect.top + triggerRect.height / 2;

    items.forEach((item) => {
      // Random position within viewport bounds
      const randomX = Math.random() * (window.innerWidth - 100) + 50 - startX;
      const randomY = Math.random() * (window.innerHeight - 100) + 50 - startY;

      // Set starting position to trigger location
      item.style.left = startX + 'px';
      item.style.top = startY + 'px';

      // Set CSS custom properties for animation
      item.style.setProperty('--random-x', randomX + 'px');
      item.style.setProperty('--random-y', randomY + 'px');
    });
  }

  function setExitPositions() {
    items.forEach((item) => {
      // Calculate exit position - fly off in random directions off screen
      const angle = Math.random() * Math.PI * 2; // Random angle
      const distance = Math.max(window.innerWidth, window.innerHeight) * 1.5; // Far enough to be off screen

      const exitX = Math.cos(angle) * distance;
      const exitY = Math.sin(angle) * distance;

      // Set CSS custom properties for exit animation
      item.style.setProperty('--exit-x', exitX + 'px');
      item.style.setProperty('--exit-y', exitY + 'px');
    });
  }

  function closeMenu() {
    if (popup.classList.contains('active')) {
      // Set random exit positions for each blob
      setExitPositions();

      // Add exiting class to trigger exit animations
      popup.classList.add('exiting');

      // Wait for all exit animations to complete
      // (5 items * 0.1s delay + 0.5s animation = ~1s total)
      setTimeout(() => {
        popup.classList.remove('active');
        popup.classList.remove('exiting');
      }, 1000);

      // Start rocket return animation
      trigger.classList.remove('active');
      trigger.classList.add('returning');

      // Remove returning class after animation completes
      setTimeout(() => {
        trigger.classList.remove('returning');
      }, 3000); // Match animation duration
    }
  }

  trigger.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const isActive = popup.classList.contains('active');
    const isReturning = trigger.classList.contains('returning');

    // Don't allow toggling while returning
    if (isReturning) return;

    if (!isActive) {
      setRandomPositions();
      trigger.classList.add('active');

      // Fire icons after rocket launch animation
      setTimeout(() => {
        popup.classList.add('active');
      }, 400); // Delay to sync with rocket launch
    } else {
      closeMenu();
    }

    console.log('Menu toggled, active:', popup.classList.contains('active'));
  });

  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.main-nav')) {
      closeMenu();
    }
  });
});
