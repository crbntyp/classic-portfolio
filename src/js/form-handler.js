/**
 * Form Handler
 * Centralized form submission handling with loading states and error management
 *
 * Usage:
 * const myForm = new FormHandler('formId', {
 *   endpoint: '/api/submit.php',
 *   onSuccess: (data) => console.log('Success!', data),
 *   onError: (message) => console.error('Error:', message),
 *   loadingType: 'button' // or 'overlay'
 * });
 */

class FormHandler {
  /**
   * Create a new form handler
   * @param {string} formId - The ID of the form element
   * @param {Object} options - Configuration options
   * @param {string} options.endpoint - API endpoint URL
   * @param {string} options.method - HTTP method (default: 'POST')
   * @param {Function} options.onSuccess - Callback on successful submission
   * @param {Function} options.onError - Callback on error
   * @param {Function} options.beforeSubmit - Callback before submission (can modify formData)
   * @param {string} options.loadingType - Loading indicator type: 'button' or 'overlay' (default: 'button')
   * @param {string} options.loadingText - Text to show while loading (default: 'Loading...')
   * @param {string} options.errorElementId - ID of error message element
   * @param {string} options.successElementId - ID of success message element
   * @param {boolean} options.resetOnSuccess - Reset form after success (default: false)
   * @param {number} options.successDelay - Delay before onSuccess callback (ms, default: 0)
   * @param {Object} options.overlayElements - IDs for overlay elements (overlay, spinner, success, text)
   */
  constructor(formId, options = {}) {
    this.form = document.getElementById(formId);

    if (!this.form) {
      console.warn(`FormHandler: Form with ID "${formId}" not found`);
      return;
    }

    this.formId = formId;
    this.options = {
      endpoint: options.endpoint || '',
      method: options.method || 'POST',
      onSuccess: options.onSuccess || null,
      onError: options.onError || null,
      beforeSubmit: options.beforeSubmit || null,
      loadingType: options.loadingType || 'button',
      loadingText: options.loadingText || 'Loading...',
      errorElementId: options.errorElementId || null,
      successElementId: options.successElementId || null,
      resetOnSuccess: options.resetOnSuccess !== false,
      successDelay: options.successDelay || 0,
      overlayElements: options.overlayElements || {}
    };

    this.submitButton = this.form.querySelector('button[type="submit"]');
    this.originalButtonText = this.submitButton ? this.submitButton.textContent : '';
    this.errorElement = this.options.errorElementId ?
      document.getElementById(this.options.errorElementId) : null;
    this.successElement = this.options.successElementId ?
      document.getElementById(this.options.successElementId) : null;

    // Overlay elements (for loadingType: 'overlay')
    this.overlayElement = this.options.overlayElements.overlay ?
      document.getElementById(this.options.overlayElements.overlay) : null;
    this.spinnerElement = this.options.overlayElements.spinner ?
      document.getElementById(this.options.overlayElements.spinner) : null;
    this.successIconElement = this.options.overlayElements.success ?
      document.getElementById(this.options.overlayElements.success) : null;
    this.textElement = this.options.overlayElements.text ?
      document.getElementById(this.options.overlayElements.text) : null;

    this._init();
  }

  /**
   * Initialize form submission handler
   * @private
   */
  _init() {
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
      this.submit();
    });
  }

  /**
   * Submit the form
   */
  async submit() {
    // Hide previous messages
    this._hideMessages();

    // Create FormData
    const formData = new FormData(this.form);

    // Allow modification before submit
    if (this.options.beforeSubmit) {
      this.options.beforeSubmit(formData);
    }

    // Show loading state
    this._showLoading();

    try {
      const response = await fetch(this.options.endpoint, {
        method: this.options.method,
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();

      if (data.success) {
        this._handleSuccess(data);
      } else {
        this._handleError(data.message || 'An error occurred');
      }
    } catch (error) {
      console.error('Form submission error:', error);
      this._handleError('An error occurred. Please try again.');
    }
  }

  /**
   * Handle successful form submission
   * @private
   */
  _handleSuccess(data) {
    if (this.options.loadingType === 'overlay') {
      // Show success state in overlay
      if (this.spinnerElement) {
        this.spinnerElement.classList.add('hidden');
      }
      if (this.successIconElement) {
        this.successIconElement.classList.add('active');
      }
      if (this.textElement) {
        this.textElement.textContent = data.message || 'Success!';
      }

      // Call success callback after delay
      if (this.options.successDelay > 0) {
        setTimeout(() => {
          this._hideLoading();
          if (this.options.resetOnSuccess) {
            this.reset();
          }
          if (this.options.onSuccess) {
            this.options.onSuccess(data);
          }
        }, this.options.successDelay);
      } else {
        this._hideLoading();
        if (this.options.resetOnSuccess) {
          this.reset();
        }
        if (this.options.onSuccess) {
          this.options.onSuccess(data);
        }
      }
    } else {
      // Button loading type
      this._hideLoading();

      // Show success message if element exists
      if (this.successElement) {
        this.successElement.textContent = data.message || 'Success!';
        this.successElement.style.display = 'block';
      }

      if (this.options.resetOnSuccess) {
        this.reset();
      }

      if (this.options.onSuccess) {
        this.options.onSuccess(data);
      }
    }
  }

  /**
   * Handle form submission error
   * @private
   */
  _handleError(message) {
    this._hideLoading();

    // Show error message
    if (this.errorElement) {
      this.errorElement.textContent = message;
      this.errorElement.style.display = 'block';
    }

    if (this.options.onError) {
      this.options.onError(message);
    }
  }

  /**
   * Show loading state
   * @private
   */
  _showLoading() {
    if (this.options.loadingType === 'overlay' && this.overlayElement) {
      // Show overlay with spinner
      this.overlayElement.classList.add('active');
      if (this.spinnerElement) {
        this.spinnerElement.classList.remove('hidden');
      }
      if (this.successIconElement) {
        this.successIconElement.classList.remove('active');
      }
      if (this.textElement) {
        this.textElement.textContent = this.options.loadingText;
      }
    } else if (this.submitButton) {
      // Disable button and change text
      this.submitButton.disabled = true;
      this.submitButton.textContent = this.options.loadingText;
    }
  }

  /**
   * Hide loading state
   * @private
   */
  _hideLoading() {
    if (this.options.loadingType === 'overlay' && this.overlayElement) {
      this.overlayElement.classList.remove('active');
      // Reset overlay state
      if (this.spinnerElement) {
        this.spinnerElement.classList.remove('hidden');
      }
      if (this.successIconElement) {
        this.successIconElement.classList.remove('active');
      }
    } else if (this.submitButton) {
      this.submitButton.disabled = false;
      this.submitButton.textContent = this.originalButtonText;
    }
  }

  /**
   * Hide error and success messages
   * @private
   */
  _hideMessages() {
    if (this.errorElement) {
      this.errorElement.style.display = 'none';
    }
    if (this.successElement) {
      this.successElement.style.display = 'none';
    }
  }

  /**
   * Reset the form
   */
  reset() {
    this.form.reset();
  }

  /**
   * Show an error message manually
   * @param {string} message - The error message to display
   */
  showError(message) {
    if (this.errorElement) {
      this.errorElement.textContent = message;
      this.errorElement.style.display = 'block';
    }
  }

  /**
   * Show a success message manually
   * @param {string} message - The success message to display
   */
  showSuccess(message) {
    if (this.successElement) {
      this.successElement.textContent = message;
      this.successElement.style.display = 'block';
    }
  }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FormHandler;
}
