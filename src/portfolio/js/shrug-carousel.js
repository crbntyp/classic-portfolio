/**
 * Shrug Carousel
 * Horizontal carousel for shrug entries when 4+ exist
 */

class ShrugCarousel {
  constructor() {
    this.container = document.getElementById('shrugEntries');
    this.prevBtn = document.getElementById('shrugNavPrev');
    this.nextBtn = document.getElementById('shrugNavNext');
    this.counter = document.getElementById('shrugNavCounter');

    if (!this.container) return;

    this.entries = this.container.querySelectorAll('.shrug-entry');
    this.totalCount = parseInt(this.container.dataset.count) || this.entries.length;
    this.visibleCount = 3;
    this.currentIndex = 0;

    // Only initialize if 4+ entries
    if (this.totalCount >= 4) {
      this.init();
    }
  }

  init() {
    if (this.prevBtn) {
      this.prevBtn.addEventListener('click', () => this.prev());
    }

    if (this.nextBtn) {
      this.nextBtn.addEventListener('click', () => this.next());
    }

    // Handle scroll events for manual scrolling
    this.container.addEventListener('scroll', () => this.onScroll());

    this.updateUI();
  }

  prev() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
      this.scrollToIndex();
    }
  }

  next() {
    const maxIndex = Math.max(0, this.totalCount - this.visibleCount);
    if (this.currentIndex < maxIndex) {
      this.currentIndex++;
      this.scrollToIndex();
    }
  }

  scrollToIndex() {
    const entry = this.entries[this.currentIndex];
    if (entry) {
      const scrollLeft = entry.offsetLeft - this.container.offsetLeft;
      this.container.scrollTo({
        left: scrollLeft,
        behavior: 'smooth'
      });
    }
    this.updateUI();
  }

  onScroll() {
    // Debounce scroll updates
    clearTimeout(this.scrollTimeout);
    this.scrollTimeout = setTimeout(() => {
      // Calculate current index based on scroll position
      const entryWidth = this.entries[0]?.offsetWidth || 0;
      const gap = 20; // Match SCSS $spacing-sm
      const scrollPos = this.container.scrollLeft;

      this.currentIndex = Math.round(scrollPos / (entryWidth + gap));
      this.updateUI();
    }, 100);
  }

  updateUI() {
    const maxIndex = Math.max(0, this.totalCount - this.visibleCount);

    // Update buttons
    if (this.prevBtn) {
      this.prevBtn.classList.toggle('is-disabled', this.currentIndex === 0);
    }

    if (this.nextBtn) {
      this.nextBtn.classList.toggle('is-disabled', this.currentIndex >= maxIndex);
    }

    // Update counter
    if (this.counter) {
      const start = this.currentIndex + 1;
      const end = Math.min(this.currentIndex + this.visibleCount, this.totalCount);
      this.counter.textContent = `${start}-${end} of ${this.totalCount}`;
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize immediately if modal is already in DOM
  new ShrugCarousel();

  // Also reinitialize when shrug modal opens (in case of dynamic content)
  const shrugModal = document.getElementById('shrugModal');
  if (shrugModal) {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class' && shrugModal.classList.contains('active')) {
          // Small delay to ensure DOM is ready
          setTimeout(() => new ShrugCarousel(), 100);
        }
      });
    });

    observer.observe(shrugModal, { attributes: true });
  }
});
