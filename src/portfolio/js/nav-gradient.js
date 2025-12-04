/**
 * Nav Gradient Hover Effect
 * Top link: cyan, bottom link: pink, in-between: gradient progression
 */

document.addEventListener('DOMContentLoaded', function() {
  const userInfo = document.querySelector('.user-info');
  if (!userInfo) return;

  const links = userInfo.querySelectorAll('.user-link');
  if (links.length === 0) return;

  // Cyan to pink
  const startColor = { r: 0, g: 255, b: 255 };   // #00ffff
  const endColor = { r: 255, g: 0, b: 150 };     // #ff0096

  links.forEach((link, index) => {
    // Calculate progress (0 = top/cyan, 1 = bottom/pink)
    const progress = links.length === 1 ? 0 : index / (links.length - 1);

    // Interpolate color
    const r = Math.round(startColor.r + (endColor.r - startColor.r) * progress);
    const g = Math.round(startColor.g + (endColor.g - startColor.g) * progress);
    const b = Math.round(startColor.b + (endColor.b - startColor.b) * progress);

    const hoverColor = `rgb(${r}, ${g}, ${b})`;

    // Set CSS custom property for hover color
    link.style.setProperty('--hover-color', hoverColor);
  });
});
