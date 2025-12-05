/**
 * Top Navigation
 * Handles the slide-down animation of the top nav after tagline fades
 */

window.showTopNav = function() {
    const nav = document.getElementById('top-nav');
    if (nav) {
        nav.classList.add('is-visible');
    }
};
