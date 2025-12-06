/**
 * Top Navigation
 * Handles the staggered fade-in animation of nav elements
 */

window.showTopNav = function() {
    const nav = document.getElementById('top-nav');
    const logo = document.getElementById('site-logo');
    const socials = document.getElementById('site-socials');

    // 1. Nav items stagger in first (handled by CSS)
    if (nav) {
        nav.classList.add('is-visible');
    }

    // 2. Social icons stagger in after nav (~600ms)
    if (socials) {
        setTimeout(() => socials.classList.add('is-visible'), 600);
    }

    // 3. Logo loads last (~1200ms)
    if (logo) {
        setTimeout(() => logo.classList.add('is-visible'), 1200);
    }
};

// Auto-show nav after a short delay on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof window.showTopNav === 'function') {
            window.showTopNav();
        }
    }, 800);
});
