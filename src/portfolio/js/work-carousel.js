/**
 * Work Carousel
 * Infinite horizontal carousel for featured work
 */

document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.getElementById('work-carousel');
  const track = carousel?.querySelector('.work-carousel__track');
  const cindersContainer = document.getElementById('cinders-container');

  if (!carousel || !track) return;

  let isActive = false;
  let animationId = null;
  let scrollPosition = 0;
  const scrollSpeed = 0.5; // pixels per frame
  const cardWidth = 320;
  const cardGap = 20;
  let singleSetWidth = 0;

  // Duplicate cards for seamless infinite scroll
  function setupInfiniteScroll() {
    const cards = Array.from(track.querySelectorAll('.work-carousel__card'));
    if (cards.length === 0) return;

    // Calculate width of one complete set (card width + gap)
    singleSetWidth = cards.length * (cardWidth + cardGap);

    // Clone the entire set twice (so we have 3 sets total: original + 2 clones)
    // This ensures we always have cards visible during the loop
    for (let i = 0; i < 2; i++) {
      cards.forEach(card => {
        const clone = card.cloneNode(true);
        track.appendChild(clone);
      });
    }
  }

  // Animate the carousel
  function animate() {
    if (!isActive) return;

    scrollPosition += scrollSpeed;

    // When we've scrolled exactly one set width, seamlessly reset to 0
    // This works because set 2 (clone 1) is now in the exact position set 1 was
    if (scrollPosition >= singleSetWidth) {
      scrollPosition = scrollPosition - singleSetWidth;
    }

    track.style.transform = `translateX(-${scrollPosition}px)`;
    animationId = requestAnimationFrame(animate);
  }

  // Show carousel
  window.showWorkCarousel = function() {
    if (isActive) return;
    isActive = true;

    // Blur and disable cinders
    if (cindersContainer) {
      cindersContainer.classList.add('is-blurred');
    }

    // Reset scroll position
    scrollPosition = 0;

    // Show carousel
    carousel.classList.add('is-active');

    // Start animation after CSS reveal animation completes (0.6s)
    setTimeout(() => {
      // Clear the CSS animation transform so JS can take over
      track.style.animation = 'none';
      track.style.opacity = '1';
      track.style.transform = 'translateX(0)';
      animate();
    }, 650);
  };

  // Hide carousel
  window.hideWorkCarousel = function() {
    if (!isActive) return;
    isActive = false;

    // Stop scroll animation
    if (animationId) {
      cancelAnimationFrame(animationId);
      animationId = null;
    }

    // Store current position for the fade animation
    // Don't reset transform - let CSS handle the scale/fade from current position
    const currentTransform = `translateX(-${scrollPosition}px)`;
    track.style.transform = currentTransform;

    // Add closing class to trigger fade out transition
    carousel.classList.add('is-closing');

    // After carousel fades away (0.6s), hide it and fade cinders back in
    setTimeout(() => {
      carousel.classList.remove('is-active');
      carousel.classList.remove('is-closing');

      // Fade cinders back in
      if (cindersContainer) {
        cindersContainer.classList.remove('is-blurred');
      }

      // Reset scroll position and styles
      scrollPosition = 0;
      track.style.transform = '';
      track.style.animation = '';
      track.style.opacity = '';
    }, 650);
  };

  // Escape key closes carousel
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isActive) {
      window.hideWorkCarousel();
    }
  });

  // Click outside track closes carousel
  carousel.addEventListener('click', function(e) {
    if (e.target === carousel) {
      window.hideWorkCarousel();
    }
  });

  // Pause on hover
  track.addEventListener('mouseenter', function() {
    if (animationId) {
      cancelAnimationFrame(animationId);
      animationId = null;
    }
  });

  track.addEventListener('mouseleave', function() {
    if (isActive) {
      animate();
    }
  });

  // Setup infinite scroll on load
  setupInfiniteScroll();
});
