/**
 * Editor Factory
 * Centralized Quill editor initialization with consistent configuration
 *
 * Usage:
 * const editor = createProjectEditor('elementId');
 * const text = editor.getText();
 * editor.setText('New content');
 */

/**
 * Create a Quill editor for project descriptions
 * @param {string} elementId - The ID of the element to attach Quill to (without #)
 * @param {Object} options - Optional configuration overrides
 * @returns {Quill} The initialized Quill editor instance
 */
function createProjectEditor(elementId, options = {}) {
  const element = document.getElementById(elementId);

  if (!element) {
    console.warn(`Editor element with ID "${elementId}" not found`);
    return null;
  }

  // Default configuration for project descriptions
  const defaultConfig = {
    theme: 'snow',
    placeholder: 'Enter project description...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link'],
        ['clean']
      ]
    }
  };

  // Merge custom options with defaults
  const config = {
    ...defaultConfig,
    ...options,
    modules: {
      ...defaultConfig.modules,
      ...(options.modules || {})
    }
  };

  // Create and return the Quill editor
  const editor = new Quill(`#${elementId}`, config);

  return editor;
}

/**
 * Create a Quill editor with custom toolbar configuration
 * @param {string} elementId - The ID of the element to attach Quill to (without #)
 * @param {Array} toolbar - Custom toolbar configuration
 * @param {Object} options - Optional configuration overrides
 * @returns {Quill} The initialized Quill editor instance
 */
function createCustomEditor(elementId, toolbar, options = {}) {
  const element = document.getElementById(elementId);

  if (!element) {
    console.warn(`Editor element with ID "${elementId}" not found`);
    return null;
  }

  const config = {
    theme: 'snow',
    placeholder: 'Enter content...',
    ...options,
    modules: {
      toolbar: toolbar,
      ...(options.modules || {})
    }
  };

  const editor = new Quill(`#${elementId}`, config);

  return editor;
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { createProjectEditor, createCustomEditor };
}
