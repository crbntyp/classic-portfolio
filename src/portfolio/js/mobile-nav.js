/**
 * Mobile Navigation
 * Handles burger menu toggle and mobile nav overlay
 */

document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('burger-menu');
    const mobileNav = document.getElementById('mobile-nav');
    const backdrop = document.getElementById('mobile-nav-backdrop');
    const mobileLinks = document.querySelectorAll('.mobile-nav__link');
    const mobileLoginTrigger = document.getElementById('mobileLoginTrigger');

    if (!burger || !mobileNav) return;

    function openMenu() {
        burger.classList.add('is-open');
        mobileNav.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        burger.classList.remove('is-open');
        mobileNav.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function toggleMenu() {
        if (mobileNav.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    // Toggle on burger click
    burger.addEventListener('click', toggleMenu);

    // Close on backdrop click
    if (backdrop) {
        backdrop.addEventListener('click', closeMenu);
    }

    // Close when clicking a nav link
    mobileLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // Handle mobile login trigger - trigger the desktop login which has the modal handler
    if (mobileLoginTrigger) {
        mobileLoginTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
            // Trigger the desktop login trigger which has the modal handler attached
            setTimeout(() => {
                const desktopLoginTrigger = document.getElementById('loginTrigger');
                if (desktopLoginTrigger) {
                    desktopLoginTrigger.click();
                }
            }, 300); // Wait for menu close animation
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileNav.classList.contains('is-open')) {
            closeMenu();
        }
    });
});
