/**
 * Modal Management
 * Handles login and add link modal open/close and form interactions
 * Refactored to use ModalManager and FormHandler classes
 */

document.addEventListener('DOMContentLoaded', function() {
  // ============================================================================
  // MODALS SETUP
  // ============================================================================

  // Login Modal
  const loginModal = new ModalManager('loginModal');
  const loginTrigger = document.getElementById('loginTrigger');
  if (loginTrigger && loginModal.modal) {
    loginTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      loginModal.open();
    });
  }

  // Forgot Password Modal
  const forgotPasswordModal = new ModalManager('forgotPasswordModal');
  const forgotPasswordLink = document.getElementById('forgotPasswordLink');
  if (forgotPasswordLink && forgotPasswordModal.modal && loginModal.modal) {
    forgotPasswordLink.addEventListener('click', (e) => {
      e.preventDefault();
      loginModal.close();
      forgotPasswordModal.open();
    });
  }

  // Back to Login Link
  const backToLogin = document.getElementById('backToLogin');
  if (backToLogin && forgotPasswordModal.modal && loginModal.modal) {
    backToLogin.addEventListener('click', (e) => {
      e.preventDefault();
      forgotPasswordModal.close();
      loginModal.open();
    });
  }

  // Add Link Modal
  const addLinkModal = new ModalManager('addLinkModal');
  const addLinkTrigger = document.getElementById('addLinkTrigger');
  if (addLinkTrigger && addLinkModal.modal) {
    addLinkTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      addLinkModal.open();
    });
  }

  // ============================================================================
  // FORMS SETUP
  // ============================================================================

  // Login Form
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const loginFormHandler = new FormHandler('loginForm', {
      endpoint: '/login.php',
      errorElementId: 'loginError',
      loadingText: 'Signing in...',
      onSuccess: (data) => {
        // Reload page to show logged-in state
        window.location.reload();
      }
    });
  }

  // Forgot Password Form
  const forgotPasswordForm = document.getElementById('forgotPasswordForm');
  if (forgotPasswordForm) {
    const forgotPasswordFormHandler = new FormHandler('forgotPasswordForm', {
      endpoint: '/forgot-password.php',
      errorElementId: 'forgotPasswordError',
      successElementId: 'forgotPasswordSuccess',
      loadingText: 'Sending...',
      resetOnSuccess: true,
      onSuccess: (data) => {
        // Close modal after 3 seconds
        setTimeout(() => {
          forgotPasswordModal.close();
          const successElement = document.getElementById('forgotPasswordSuccess');
          if (successElement) {
            successElement.style.display = 'none';
          }
        }, 3000);
      }
    });
  }

  // ============================================================================
  // QUILL EDITOR
  // ============================================================================

  let quillEditor = null;
  const quillContainer = document.getElementById('projectDescription');

  if (quillContainer) {
    quillEditor = createProjectEditor('projectDescription');
  }

  // ============================================================================
  // CATEGORY & IMAGE PREVIEW SETUP
  // ============================================================================

  // Setup category toggle
  setupCategoryToggle({
    categorySelectId: 'category',
    imageUploadGroupId: 'imageUploadGroup',
    imageInputId: 'projectImage',
    imagePreviewId: 'imagePreview',
    fileNameDisplayId: 'fileName'
  });

  // Setup image preview
  setupImagePreview({
    imageInputId: 'projectImage',
    imagePreviewId: 'imagePreview',
    fileNameDisplayId: 'fileName'
  });

  // ============================================================================
  // ADD LINK FORM
  // ============================================================================

  const addLinkForm = document.getElementById('addLinkForm');
  if (addLinkForm) {
    const addLinkFormHandler = new FormHandler('addLinkForm', {
      endpoint: '/admin/add-link.php',
      errorElementId: 'addLinkError',
      loadingType: 'overlay',
      loadingText: 'Uploading...',
      successDelay: 2500,
      overlayElements: {
        overlay: 'uploadOverlay',
        spinner: 'uploadSpinner',
        success: 'uploadSuccess',
        text: 'uploadText'
      },
      beforeSubmit: (formData) => {
        // Add Quill editor content to formData
        if (quillEditor) {
          const description = quillEditor.getText().trim();
          formData.set('projectDescription', description);
        }
      },
      onSuccess: (data) => {
        // If we're on the admin page, reload to show the new project
        if (window.location.pathname.includes('/admin/')) {
          window.location.reload();
        } else {
          // Otherwise just reset the form (for modal usage elsewhere)
          addLinkForm.reset();
          if (imagePreview) {
            imagePreview.innerHTML = `
              <div class="image-preview-placeholder">
                <i class="las la-image"></i>
                <span>Image Preview</span>
              </div>
            `;
          }
          if (fileNameDisplay) {
            fileNameDisplay.textContent = 'No file chosen';
          }
          if (imageUploadGroup) {
            imageUploadGroup.style.display = 'none';
          }
          if (quillEditor) {
            quillEditor.setText('');
          }
        }
      }
    });
  }
});
