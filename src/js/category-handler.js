/**
 * Category Handler
 * Centralized category-to-image-upload toggle logic
 *
 * Handles showing/hiding image upload fields based on category selection.
 * Classic portfolio requires image uploads, blob categories do not.
 *
 * Usage:
 * setupCategoryToggle({
 *   categorySelectId: 'category',
 *   imageUploadGroupId: 'imageUploadGroup',
 *   imageInputId: 'projectImage',
 *   imagePreviewId: 'imagePreview',
 *   fileNameDisplayId: 'fileName'
 * });
 */

/**
 * Setup category toggle functionality
 * @param {Object} config - Configuration object
 * @param {string} config.categorySelectId - ID of the category select element
 * @param {string} config.imageUploadGroupId - ID of the image upload group container
 * @param {string} config.imageInputId - ID of the image file input
 * @param {string} config.imagePreviewId - ID of the image preview element (optional)
 * @param {string} config.fileNameDisplayId - ID of the filename display element (optional)
 */
function setupCategoryToggle(config) {
  const {
    categorySelectId,
    imageUploadGroupId,
    imageInputId,
    imagePreviewId,
    fileNameDisplayId
  } = config;

  // Get elements
  const categorySelect = document.getElementById(categorySelectId);
  const imageUploadGroup = document.getElementById(imageUploadGroupId);
  const imageInput = document.getElementById(imageInputId);
  const imagePreview = imagePreviewId ? document.getElementById(imagePreviewId) : null;
  const fileNameDisplay = fileNameDisplayId ? document.getElementById(fileNameDisplayId) : null;

  // Check if required elements exist
  if (!categorySelect || !imageUploadGroup) {
    console.warn('Category toggle: Required elements not found', {
      categorySelect: !!categorySelect,
      imageUploadGroup: !!imageUploadGroup
    });
    return;
  }

  // Blob categories that don't require images
  const BLOB_CATEGORIES = ['recent-artwork', 'artwork-portfolio', 'vibes'];

  // Category change handler
  categorySelect.addEventListener('change', function() {
    const selectedCategory = this.value;

    if (selectedCategory === 'classic-portfolio') {
      // Show image upload for classic portfolio
      imageUploadGroup.style.display = 'block';

      if (imageInput) {
        imageInput.required = true;
      }

    } else if (BLOB_CATEGORIES.includes(selectedCategory)) {
      // Hide image upload for blob categories
      imageUploadGroup.style.display = 'none';

      // Reset image fields
      if (imageInput) {
        imageInput.required = false;
        imageInput.value = '';
      }

      // Reset preview
      if (imagePreview) {
        imagePreview.innerHTML = '';
      }

      // Reset filename display
      if (fileNameDisplay) {
        fileNameDisplay.textContent = 'No file chosen';
      }

    } else {
      // No category selected or unknown category
      imageUploadGroup.style.display = 'none';

      if (imageInput) {
        imageInput.required = false;
        imageInput.value = '';
      }

      if (imagePreview) {
        imagePreview.innerHTML = '';
      }

      if (fileNameDisplay) {
        fileNameDisplay.textContent = 'No file chosen';
      }
    }
  });

  // Return the category select element for chaining if needed
  return categorySelect;
}

/**
 * Check if a category is a blob category
 * @param {string} category - The category value to check
 * @returns {boolean} True if category is a blob category
 */
function isBlobCategory(category) {
  const BLOB_CATEGORIES = ['recent-artwork', 'artwork-portfolio', 'vibes'];
  return BLOB_CATEGORIES.includes(category);
}

/**
 * Check if a category requires an image upload
 * @param {string} category - The category value to check
 * @returns {boolean} True if category requires image upload
 */
function requiresImageUpload(category) {
  return category === 'classic-portfolio';
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { setupCategoryToggle, isBlobCategory, requiresImageUpload };
}
