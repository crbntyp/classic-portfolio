/**
 * Shrug Masonry
 * Masonry grid layout for shrug entries with date filtering
 */

class ShrugMasonry {
  constructor() {
    this.container = document.getElementById('shrugEntries');
    this.overlayContent = document.querySelector('.shrug-overlay-content');
    this.shrugList = document.getElementById('shrugList');
    this.filterContainer = document.getElementById('shrugFilter');
    this.tagFilterContainer = document.getElementById('shrugTagFilter');
    this.activeTagDisplay = document.getElementById('shrugActiveTag');
    this.clearTagBtn = document.getElementById('shrugClearTag');

    // Reader panel elements
    this.reader = document.getElementById('shrugReader');
    this.readerClose = document.getElementById('shrugReaderClose');
    this.readerAvatar = document.getElementById('shrugReaderAvatar');
    this.readerTitle = document.getElementById('shrugReaderTitle');
    this.readerMeta = document.getElementById('shrugReaderMeta');
    this.readerContent = document.getElementById('shrugReaderContent');
    this.readerTags = document.getElementById('shrugReaderTags');

    this.masonry = null;
    this.expandedEntry = null;
    this.activeTag = null;

    if (!this.container) return;

    this.entries = this.container.querySelectorAll('.shrug-entry');
    if (this.entries.length === 0) return;

    this.init();
    this.initFilter();
    this.initTagFilter();
    this.initExpansion();
    this.handleDeepLink();
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
        // Clear any active tag filter first
        if (this.activeTag) {
          this.activeTag = null;
          this.container.querySelectorAll('.shrug-tag').forEach(t => t.classList.remove('is-active'));
          if (this.tagFilterContainer) {
            this.tagFilterContainer.classList.remove('is-active');
          }
        }

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

  // Initialize tag filtering
  initTagFilter() {
    // Click handler for all tag buttons (using event delegation)
    this.container.addEventListener('click', (e) => {
      if (e.target.classList.contains('shrug-tag')) {
        e.stopPropagation(); // Don't trigger card expansion
        const tag = e.target.dataset.tag;
        this.filterByTag(tag);
      }
    });

    // Clear tag filter button
    if (this.clearTagBtn) {
      this.clearTagBtn.addEventListener('click', () => {
        this.clearTagFilter();
      });
    }
  }

  // Filter entries by tag
  filterByTag(tag) {
    this.activeTag = tag;

    // Close reader if open
    if (this.expandedEntry) {
      this.closeReader();
    }

    // Show/hide entries based on tag
    this.entries.forEach(entry => {
      const entryTags = entry.dataset.tags || '';
      const tagsArray = entryTags.split(',').map(t => t.trim());

      if (tagsArray.includes(tag)) {
        entry.classList.remove('is-hidden');
      } else {
        entry.classList.add('is-hidden');
      }
    });

    // Update active state on tag buttons
    this.container.querySelectorAll('.shrug-tag').forEach(btn => {
      if (btn.dataset.tag === tag) {
        btn.classList.add('is-active');
      } else {
        btn.classList.remove('is-active');
      }
    });

    // Show tag filter indicator
    if (this.tagFilterContainer && this.activeTagDisplay) {
      this.activeTagDisplay.textContent = tag;
      this.tagFilterContainer.classList.add('is-active');
    }

    // Reset date filter to "All"
    if (this.filterContainer) {
      const buttons = this.filterContainer.querySelectorAll('.shrug-filter__btn');
      buttons.forEach(btn => {
        btn.classList.remove('shrug-filter__btn--active');
        if (btn.dataset.filter === 'all') {
          btn.classList.add('shrug-filter__btn--active');
        }
      });
    }

    // Refresh masonry layout
    this.refresh();
  }

  // Clear tag filter
  clearTagFilter() {
    this.activeTag = null;

    // Show all entries
    this.entries.forEach(entry => {
      entry.classList.remove('is-hidden');
    });

    // Remove active state from tag buttons
    this.container.querySelectorAll('.shrug-tag').forEach(btn => {
      btn.classList.remove('is-active');
    });

    // Hide tag filter indicator
    if (this.tagFilterContainer) {
      this.tagFilterContainer.classList.remove('is-active');
    }

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

  // Initialize expansion click handlers
  initExpansion() {
    // Entry click opens reader
    this.entries.forEach(entry => {
      entry.addEventListener('click', (e) => {
        // Don't open if clicking a tag
        if (e.target.classList.contains('shrug-tag')) return;

        this.openReader(entry);
      });
    });

    // Reader close button
    if (this.readerClose) {
      this.readerClose.addEventListener('click', () => {
        this.closeReader();
      });
    }

    // ESC key to close reader
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.expandedEntry) {
        this.closeReader();
      }
    });
  }

  // Open reader panel with entry content
  openReader(entry) {
    const slug = entry.dataset.slug;
    if (!slug || !this.reader) return;

    // Get content from the entry
    const title = entry.querySelector('.shrug-entry__title')?.textContent || '';
    const date = entry.querySelector('.shrug-entry__date')?.innerHTML || '';
    const avatar = entry.querySelector('.shrug-entry__avatar')?.src || '';
    const content = entry.dataset.fullContent || entry.querySelector('.shrug-entry__content')?.innerHTML || '';
    const tagsContainer = entry.querySelector('.shrug-entry__tags');

    // Populate reader
    if (this.readerAvatar) this.readerAvatar.src = avatar;
    if (this.readerTitle) this.readerTitle.textContent = title;
    if (this.readerMeta) this.readerMeta.innerHTML = date;
    if (this.readerContent) this.readerContent.innerHTML = content;

    // Copy tags
    if (this.readerTags && tagsContainer) {
      this.readerTags.innerHTML = tagsContainer.innerHTML;
    } else if (this.readerTags) {
      this.readerTags.innerHTML = '';
    }

    // Mark states
    this.shrugList.classList.add('has-reader-open');
    this.expandedEntry = entry;

    // Open reader panel
    if (this.overlayContent) this.overlayContent.classList.add('has-reader');
    this.reader.classList.add('is-open');

    // Relayout masonry after transition
    setTimeout(() => {
      this.refresh();
    }, 350);

    // Update URL
    history.pushState({ shrug: slug }, '', `#shrug/${slug}`);
  }

  // Close reader panel
  closeReader() {
    if (!this.reader) return;

    // Remove states
    this.reader.classList.remove('is-open');
    this.shrugList.classList.remove('has-reader-open');

    if (this.overlayContent) {
      this.overlayContent.classList.remove('has-reader');
    }

    this.expandedEntry = null;

    // Relayout masonry after transition
    setTimeout(() => {
      this.refresh();
    }, 350);

    // Update URL - remove the hash
    history.pushState({}, '', window.location.pathname);
  }

  // Handle deep linking on page load
  handleDeepLink() {
    const hash = window.location.hash;
    if (!hash.startsWith('#shrug/')) return;

    const slug = hash.replace('#shrug/', '');
    if (!slug) return;

    // Find the entry with this slug
    const entry = this.container.querySelector(`[data-slug="${slug}"]`);
    if (!entry) return;

    // Open the shrug modal first if not already open
    const shrugModal = document.getElementById('shrugModal');
    const shrugBtn = document.querySelector('[data-modal="shrugModal"]');

    if (shrugModal && !shrugModal.classList.contains('active')) {
      // Trigger the modal open
      if (shrugBtn) {
        shrugBtn.click();
      } else {
        shrugModal.classList.add('active');
        document.body.classList.add('modal-open');
      }

      // Wait for modal animation, then open reader
      setTimeout(() => {
        this.openReader(entry);
      }, 300);
    } else {
      // Modal already open, just open reader
      this.openReader(entry);
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
