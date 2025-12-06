/**
 * Shrug Masonry
 * Masonry grid layout for shrug entries with date filtering
 */

class ShrugMasonry {
  constructor() {
    this.container = document.getElementById('shrugEntries');
    this.filterContainer = document.getElementById('shrugFilter');
    this.masonry = null;

    if (!this.container) return;

    this.entries = this.container.querySelectorAll('.shrug-entry');
    if (this.entries.length === 0) return;

    this.init();
    this.initFilter();
  }

  init() {
    // Initialize Masonry
    if (typeof Masonry !== 'undefined') {
      this.masonry = new Masonry(this.container, {
        itemSelector: '.shrug-entry:not(.is-hidden)',
        columnWidth: '.shrug-entry:not(.is-hidden)',
        percentPosition: true,
        gutter: 20,
        transitionDuration: '0.3s'
      });
    }
  }

  initFilter() {
    if (!this.filterContainer) return;

    const buttons = this.filterContainer.querySelectorAll('.shrug-filter__btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Update active state
        buttons.forEach(b => b.classList.remove('shrug-filter__btn--active'));
        btn.classList.add('shrug-filter__btn--active');

        // Filter entries
        const filter = btn.dataset.filter;
        this.filterEntries(filter);
      });
    });
  }

  filterEntries(filter) {
    this.entries.forEach(entry => {
      if (filter === 'all' || entry.dataset.month === filter) {
        entry.classList.remove('is-hidden');
      } else {
        entry.classList.add('is-hidden');
      }
    });

    // Refresh masonry layout
    this.refresh();
  }

  // Reinitialize masonry (useful after content changes)
  refresh() {
    if (this.masonry) {
      this.masonry.reloadItems();
      this.masonry.layout();
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize immediately if modal is already in DOM
  let shrugMasonry = new ShrugMasonry();

  // Also reinitialize when shrug modal opens
  const shrugModal = document.getElementById('shrugModal');
  if (shrugModal) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class' && shrugModal.classList.contains('active')) {
          // Small delay to ensure DOM is ready
          setTimeout(() => {
            shrugMasonry = new ShrugMasonry();
            if (shrugMasonry.masonry) {
              shrugMasonry.refresh();
            }
          }, 100);
        }
      });
    });

    observer.observe(shrugModal, { attributes: true });
  }
});
