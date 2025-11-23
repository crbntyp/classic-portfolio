/**
 * Category Handler
 * Centralized category handling logic
 *
 * All categories now support optional image uploads.
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

  // Check if required elements exist
  if (!categorySelect || !imageUploadGroup) {
    console.warn('Category toggle: Required elements not found', {
      categorySelect: !!categorySelect,
      imageUploadGroup: !!imageUploadGroup
    });
    return;
  }

  // Valid categories
  const VALID_CATEGORIES = ['classic-portfolio', 'recent-artwork', 'artwork-portfolio', 'vibes'];

  // Category change handler - always show image upload for valid categories
  categorySelect.addEventListener('change', function() {
    const selectedCategory = this.value;

    if (VALID_CATEGORIES.includes(selectedCategory)) {
      // Show image upload for all valid categories (optional upload)
      imageUploadGroup.style.display = 'block';
      if (imageInput) {
        imageInput.required = false; // Images are optional
      }
    } else {
      // No category selected
      imageUploadGroup.style.display = 'none';
      if (imageInput) {
        imageInput.required = false;
      }
    }
  });

  // Return the category select element for chaining if needed
  return categorySelect;
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { setupCategoryToggle };
}
