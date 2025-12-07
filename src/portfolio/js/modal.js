/**
 * Modal Management
 * Handles login and about us modal open/close and form interactions
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

  // About Us Modal
  const aboutUsModal = new ModalManager('aboutUsModal');
  const aboutUsTrigger = document.getElementById('aboutUsTrigger');
  if (aboutUsTrigger && aboutUsModal.modal) {
    aboutUsTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      aboutUsModal.open();
      aboutUsTrigger.classList.add('is-active');
    });

    // Remove active state when modal closes via close button
    const aboutCloseBtn = aboutUsModal.modal.querySelector('.modal__close');
    if (aboutCloseBtn) {
      aboutCloseBtn.addEventListener('click', () => {
        aboutUsTrigger.classList.remove('is-active');
      });
    }
  }

  // ============================================================================
  // FORMS SETUP
  // ============================================================================

  // Login Form
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const loginFormHandler = new FormHandler('loginForm', {
      endpoint: '/portfolio/login.php',
      errorElementId: 'loginError',
      loadingText: 'Signing in...',
      onSuccess: (data) => {
        // Reload page to show logged-in state
        window.location.reload();
      }
    });
  }

});
