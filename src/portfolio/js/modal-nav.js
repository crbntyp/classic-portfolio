/**
 * Modal Navigation Handler
 * Connects nav links to their respective modals
 */

document.addEventListener('DOMContentLoaded', function() {
  // Modal elements and their managers
  const modals = {
    shrug: {
      el: document.getElementById('shrugModal'),
      close: document.getElementById('shrugModalClose')
    },
    services: {
      el: document.getElementById('servicesModal'),
      close: document.getElementById('servicesModalClose')
    },
    contact: {
      el: document.getElementById('contactModal'),
      close: document.getElementById('contactModalClose')
    },
    about: {
      el: document.getElementById('aboutUsModal'),
      close: document.getElementById('aboutUsModalClose')
    }
  };

  // Map hash links to modal keys
  const hashMap = {
    '#shrug': 'shrug',
    '#services': 'services',
    '#contact': 'contact',
    '#about': 'about'
  };

  // Open modal function
  function openModal(key) {
    const modal = modals[key];
    if (modal && modal.el) {
      modal.el.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  // Close modal function
  function closeModal(key) {
    const modal = modals[key];
    if (modal && modal.el) {
      modal.el.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  // Close all modals
  function closeAllModals() {
    Object.keys(modals).forEach(key => closeModal(key));
  }

  // Setup close buttons
  Object.keys(modals).forEach(key => {
    const modal = modals[key];

    // Close button click
    if (modal.close) {
      modal.close.addEventListener('click', () => closeModal(key));
    }

    // Overlay click (close when clicking outside content)
    if (modal.el) {
      const overlay = modal.el.querySelector('.modal__overlay');
      if (overlay) {
        overlay.addEventListener('click', (e) => {
          // Only close if clicking the overlay itself, not its children
          if (e.target === overlay) {
            closeModal(key);
          }
        });
      }
    }
  });

  // Handle nav link clicks
  document.querySelectorAll('.top-nav__link, .mobile-nav__link, .mobile-cta').forEach(link => {
    const href = link.getAttribute('href');

    // Featured Work - under construction
    if (href === '#work') {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Under construction');
      });
    }
    // Modal links
    else if (hashMap[href]) {
      link.addEventListener('click', (e) => {
        e.preventDefault();

        // Close mobile nav if open
        const mobileNav = document.getElementById('mobile-nav');
        const burger = document.getElementById('burger-menu');
        if (mobileNav && mobileNav.classList.contains('is-open')) {
          mobileNav.classList.remove('is-open');
          if (burger) burger.classList.remove('is-open');
          document.body.style.overflow = '';

          // Delay modal open to allow nav close animation
          setTimeout(() => openModal(hashMap[href]), 300);
        } else {
          openModal(hashMap[href]);
        }
      });
    }
  });

  // Handle deep links (page load with hash)
  if (window.location.hash && hashMap[window.location.hash]) {
    // Wait for page animations to complete
    setTimeout(() => {
      openModal(hashMap[window.location.hash]);
    }, 1500);
  }

  // Escape key closes modals
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeAllModals();
    }
  });

  // Cross-modal navigation: Services -> Contact with tier pre-selected
  document.querySelectorAll('.pricing-card__cta').forEach(btn => {
    btn.addEventListener('click', () => {
      const tier = btn.dataset.tier;

      // Close services modal
      closeModal('services');

      // Open contact modal with tier pre-selected
      setTimeout(() => {
        // Update tier selection
        const tierOptions = document.querySelectorAll('.tier-option');
        const selectedTierInput = document.getElementById('selectedTier');

        tierOptions.forEach(opt => {
          opt.classList.toggle('tier-option--selected', opt.dataset.tier === tier);
        });

        if (selectedTierInput) {
          selectedTierInput.value = tier;
        }

        openModal('contact');
      }, 300);
    });
  });
});
