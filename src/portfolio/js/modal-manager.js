/**
 * ModalManager Class
 * Reusable modal management for consistent open/close behavior
 *
 * Usage:
 *   const myModal = new ModalManager('myModalId', {
 *     closeButtonSelector: '.modal__close',
 *     onOpen: () => console.log('opened'),
 *     onClose: () => console.log('closed')
 *   });
 *
 *   myModal.open();
 *   myModal.close();
 */

class ModalManager {
  /**
   * Create a new modal manager
   * @param {string} modalId - The ID of the modal element
   * @param {Object} options - Configuration options
   * @param {string} options.closeButtonSelector - CSS selector for close button (default: '.modal__close')
   * @param {string} options.overlaySelector - CSS selector for overlay (default: '.modal__overlay')
   * @param {Function} options.onOpen - Callback when modal opens
   * @param {Function} options.onClose - Callback when modal closes
   * @param {boolean} options.closeOnEscape - Close on Escape key (default: true)
   */
  constructor(modalId, options = {}) {
    this.modal = document.getElementById(modalId);

    if (!this.modal) {
      console.warn(`ModalManager: Modal with ID "${modalId}" not found`);
      return;
    }

    // Configuration
    this.options = {
      closeButtonSelector: '.modal__close',
      overlaySelector: '.modal__overlay',
      onOpen: null,
      onClose: null,
      closeOnEscape: true,
      ...options
    };

    // Get modal elements
    this.closeBtn = this.modal.querySelector(this.options.closeButtonSelector);
    this.overlay = this.modal.querySelector(this.options.overlaySelector);

    // Bind methods to preserve 'this' context
    this.open = this.open.bind(this);
    this.close = this.close.bind(this);
    this._handleEscape = this._handleEscape.bind(this);

    // Setup event listeners
    this._setupEventListeners();

    // Track if modal is open
    this.isModalOpen = false;
  }

  /**
   * Setup event listeners for close button and overlay
   * @private
   */
  _setupEventListeners() {
    if (this.closeBtn) {
      this.closeBtn.addEventListener('click', this.close);
    }

    if (this.overlay) {
      this.overlay.addEventListener('click', this.close);
    }

    // Global escape key handler (only when modal is open)
    if (this.options.closeOnEscape) {
      document.addEventListener('keydown', this._handleEscape);
    }
  }

  /**
   * Handle Escape key press
   * @private
   */
  _handleEscape(e) {
    if (e.key === 'Escape' && this.isOpen()) {
      this.close();
    }
  }

  /**
   * Open the modal
   */
  open() {
    if (!this.modal) return;

    this.modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    this.isModalOpen = true;

    if (this.options.onOpen) {
      this.options.onOpen();
    }
  }

  /**
   * Close the modal
   */
  close() {
    if (!this.modal) return;

    this.modal.classList.remove('active');
    document.body.style.overflow = '';
    this.isModalOpen = false;

    if (this.options.onClose) {
      this.options.onClose();
    }
  }

  /**
   * Toggle modal open/closed
   */
  toggle() {
    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  }

  /**
   * Check if modal is currently open
   * @returns {boolean}
   */
  isOpen() {
    return this.modal && this.modal.classList.contains('active');
  }

  /**
   * Destroy the modal manager and remove event listeners
   */
  destroy() {
    if (this.closeBtn) {
      this.closeBtn.removeEventListener('click', this.close);
    }

    if (this.overlay) {
      this.overlay.removeEventListener('click', this.close);
    }

    if (this.options.closeOnEscape) {
      document.removeEventListener('keydown', this._handleEscape);
    }
  }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ModalManager;
}
