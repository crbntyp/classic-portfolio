/**
 * Smart Tooltip Positioning
 * Automatically positions tooltips based on viewport edges
 */

document.addEventListener('DOMContentLoaded', function() {
  const tooltipElements = document.querySelectorAll('[data-tooltip]');

  // Function to calculate best position for a tooltip
  function calculateBestPosition(element) {
    const rect = element.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // Calculate distances from edges
    const spaceTop = rect.top;
    const spaceBottom = viewportHeight - rect.bottom;
    const spaceLeft = rect.left;
    const spaceRight = viewportWidth - rect.right;

    // Minimum space needed for tooltip (approximate)
    const minSpace = 80;

    // Determine best position
    let bestPosition = 'top'; // default

    // Priority: top > bottom > right > left
    if (spaceTop >= minSpace) {
      bestPosition = 'top';
    } else if (spaceBottom >= minSpace) {
      bestPosition = 'bottom';
    } else if (spaceRight >= minSpace) {
      bestPosition = 'right';
    } else if (spaceLeft >= minSpace) {
      bestPosition = 'left';
    } else {
      // If no good space, use the largest available
      const spaces = {
        top: spaceTop,
        bottom: spaceBottom,
        left: spaceLeft,
        right: spaceRight
      };
      bestPosition = Object.keys(spaces).reduce((a, b) =>
        spaces[a] > spaces[b] ? a : b
      );
    }

    return bestPosition;
  }

  tooltipElements.forEach(element => {
    // Skip if position is manually locked
    const currentPosition = element.getAttribute('data-tooltip-position');
    const locked = element.getAttribute('data-tooltip-locked');

    if (!currentPosition || currentPosition === 'auto') {
      // Set initial position on page load (without triggering transitions)
      const bestPosition = calculateBestPosition(element);
      element.setAttribute('data-tooltip-position', bestPosition);
    } else if (locked === 'true') {
      // Position is locked, don't recalculate
      return;
    }

    // Recalculate on mouseenter in case viewport has changed
    element.addEventListener('mouseenter', function() {
      const currentPosition = this.getAttribute('data-tooltip-position');
      const locked = this.getAttribute('data-tooltip-locked');

      // Skip if locked
      if (locked === 'true') return;

      const bestPosition = calculateBestPosition(this);

      // Only update if position changed (prevents unnecessary reflows)
      if (currentPosition !== bestPosition) {
        this.setAttribute('data-tooltip-position', bestPosition);
      }
    });
  });
});
