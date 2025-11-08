/**
 * Image Preview Handler
 * Centralized image preview and filename display logic
 *
 * Handles displaying image previews and filenames when user selects a file.
 *
 * Usage:
 * setupImagePreview({
 *   imageInputId: 'projectImage',
 *   imagePreviewId: 'imagePreview',
 *   fileNameDisplayId: 'fileName'
 * });
 */

/**
 * Setup image preview functionality
 * @param {Object} config - Configuration object
 * @param {string} config.imageInputId - ID of the file input element
 * @param {string} config.imagePreviewId - ID of the preview container element
 * @param {string} config.fileNameDisplayId - ID of the filename display element (optional)
 * @param {string} config.placeholderHtml - Custom placeholder HTML (optional)
 */
function setupImagePreview(config) {
  const {
    imageInputId,
    imagePreviewId,
    fileNameDisplayId,
    placeholderHtml
  } = config;

  // Get elements
  const imageInput = document.getElementById(imageInputId);
  const imagePreview = document.getElementById(imagePreviewId);
  const fileNameDisplay = fileNameDisplayId ? document.getElementById(fileNameDisplayId) : null;

  // Check if required elements exist
  if (!imageInput || !imagePreview) {
    console.warn('Image preview: Required elements not found', {
      imageInput: !!imageInput,
      imagePreview: !!imagePreview
    });
    return;
  }

  // Default placeholder HTML
  const defaultPlaceholder = `
    <div class="image-preview-placeholder">
      <i class="las la-image"></i>
      <span>Image Preview</span>
    </div>
  `;

  const placeholder = placeholderHtml || defaultPlaceholder;

  // File input change handler
  imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];

    if (file) {
      // Update filename display
      if (fileNameDisplay) {
        fileNameDisplay.textContent = file.name;
      }

      // Show preview if it's an image
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = function(e) {
          imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };

        reader.readAsDataURL(file);
      } else {
        // Not an image file, show placeholder
        imagePreview.innerHTML = placeholder;
      }

    } else {
      // No file selected, reset to placeholder
      if (fileNameDisplay) {
        fileNameDisplay.textContent = 'No file chosen';
      }

      imagePreview.innerHTML = placeholder;
    }
  });

  // Return the input element for chaining if needed
  return imageInput;
}

/**
 * Manually set an image preview from a URL
 * @param {string} previewElementId - ID of the preview container element
 * @param {string} imageUrl - URL of the image to display
 * @param {string} altText - Alt text for the image
 */
function setImagePreview(previewElementId, imageUrl, altText = 'Preview') {
  const previewElement = document.getElementById(previewElementId);

  if (!previewElement) {
    console.warn(`Preview element with ID "${previewElementId}" not found`);
    return;
  }

  if (imageUrl) {
    previewElement.innerHTML = `<img src="${imageUrl}" alt="${altText}">`;
  } else {
    clearImagePreview(previewElementId);
  }
}

/**
 * Clear the image preview and show placeholder
 * @param {string} previewElementId - ID of the preview container element
 * @param {string} placeholderHtml - Custom placeholder HTML (optional)
 */
function clearImagePreview(previewElementId, placeholderHtml) {
  const previewElement = document.getElementById(previewElementId);

  if (!previewElement) {
    console.warn(`Preview element with ID "${previewElementId}" not found`);
    return;
  }

  const defaultPlaceholder = `
    <div class="image-preview-placeholder">
      <i class="las la-image"></i>
      <span>Image Preview</span>
    </div>
  `;

  previewElement.innerHTML = placeholderHtml || defaultPlaceholder;
}

/**
 * Reset file input and clear preview
 * @param {string} imageInputId - ID of the file input element
 * @param {string} imagePreviewId - ID of the preview container element
 * @param {string} fileNameDisplayId - ID of the filename display element (optional)
 */
function resetImageInput(imageInputId, imagePreviewId, fileNameDisplayId) {
  const imageInput = document.getElementById(imageInputId);
  const fileNameDisplay = fileNameDisplayId ? document.getElementById(fileNameDisplayId) : null;

  if (imageInput) {
    imageInput.value = '';
  }

  if (fileNameDisplay) {
    fileNameDisplay.textContent = 'No file chosen';
  }

  clearImagePreview(imagePreviewId);
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    setupImagePreview,
    setImagePreview,
    clearImagePreview,
    resetImageInput
  };
}
