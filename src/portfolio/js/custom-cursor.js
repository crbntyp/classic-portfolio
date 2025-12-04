/**
 * Custom Cursor
 * Circle cursor that follows the mouse with trailing dot
 */

document.addEventListener('DOMContentLoaded', function() {
  // Only on home page
  if (!document.body.classList.contains('home-body')) return;

  // Create cursor ring
  const cursor = document.createElement('div');
  cursor.className = 'custom-cursor';
  document.body.appendChild(cursor);

  // Create trailing dot
  const dot = document.createElement('div');
  dot.className = 'custom-cursor-dot';
  document.body.appendChild(dot);

  // Mouse position
  let mouseX = 0;
  let mouseY = 0;

  // Dot position (trails behind)
  let dotX = 0;
  let dotY = 0;

  // Track mouse position
  document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    cursor.style.left = mouseX + 'px';
    cursor.style.top = mouseY + 'px';
  });

  // Animate dot to chase cursor
  function animateDot() {
    // Ease towards mouse position
    dotX += (mouseX - dotX) * 0.15;
    dotY += (mouseY - dotY) * 0.15;

    dot.style.left = dotX + 'px';
    dot.style.top = dotY + 'px';

    requestAnimationFrame(animateDot);
  }
  animateDot();

  // Hide when mouse leaves window
  document.addEventListener('mouseleave', () => {
    cursor.style.opacity = '0';
    dot.style.opacity = '0';
  });

  document.addEventListener('mouseenter', () => {
    cursor.style.opacity = '0.25';
    dot.style.opacity = '0.5';
  });
});
