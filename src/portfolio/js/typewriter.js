/**
 * Typewriter Effect for Tagline
 * Types phrases with animated loading dots between them
 */

document.addEventListener('DOMContentLoaded', function() {
  const textEl = document.querySelector('.tagline-text');
  const dotsEl = document.querySelector('.tagline-dots');

  if (!textEl || !dotsEl) return;

  const phrases = [
    { text: 'thinking', dots: true },
    { text: 'Engineering Manager', dots: true },
    { text: 'Award winning UI Designer', dots: true },
    { text: 'UI Engineer', dots: true },
    { text: 'People Leader', dots: true },
    { text: 'hello@crbntyp.com', dots: true, isEmail: true },
    { text: 'loaded', dots: true, final: true }
  ];

  const TYPING_SPEED = 150;
  const DELETE_SPEED = 60;
  const DOTS_DURATION = 2000;
  const PAUSE_AFTER_TYPING = 800;
  const CYCLE_DURATION = 240000; // 1 minute

  let currentPhrase = 0;
  let cycleStartTime = Date.now();

  // Animate dots (show/hide one after another)
  function animateDots(duration) {
    return new Promise(resolve => {
      dotsEl.innerHTML = '<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>';
      dotsEl.classList.add('animating');

      setTimeout(() => {
        dotsEl.classList.remove('animating');
        dotsEl.innerHTML = '';
        resolve();
      }, duration);
    });
  }

  // Type text character by character
  function typeText(text) {
    return new Promise(resolve => {
      let i = 0;
      textEl.textContent = '';

      function type() {
        if (i < text.length) {
          textEl.textContent += text.charAt(i);
          i++;
          setTimeout(type, TYPING_SPEED);
        } else {
          resolve();
        }
      }
      type();
    });
  }

  // Delete text character by character
  function deleteText() {
    return new Promise(resolve => {
      function del() {
        if (textEl.textContent.length > 0) {
          textEl.textContent = textEl.textContent.slice(0, -1);
          setTimeout(del, DELETE_SPEED);
        } else {
          resolve();
        }
      }
      del();
    });
  }

  // Add cyan full stop for final phrase
  function addCyanDot() {
    const dot = document.createElement('span');
    dot.className = 'cyan-dot';
    dot.textContent = '.';
    textEl.appendChild(dot);
  }

  // Type email as a link
  function typeEmailLink(email) {
    return new Promise(resolve => {
      textEl.innerHTML = '';
      const link = document.createElement('a');
      link.href = 'mailto:' + email;
      link.className = 'tagline-link';
      textEl.appendChild(link);

      let i = 0;
      function type() {
        if (i < email.length) {
          link.textContent += email.charAt(i);
          i++;
          setTimeout(type, TYPING_SPEED);
        } else {
          resolve();
        }
      }
      type();
    });
  }

  // Loading indicator element
  const loadingIndicator = document.getElementById('loadingIndicator');

  // Show loading indicator
  function showLoading() {
    if (loadingIndicator) {
      loadingIndicator.classList.add('visible');
    }
  }

  // Hide loading indicator
  function hideLoading() {
    if (loadingIndicator) {
      loadingIndicator.classList.remove('visible');
    }
  }

  // Show final state immediately (skip animation) - hide tagline completely
  function showFinalState() {
    // For returning visitors, just hide the tagline entirely
    const taglineEl = document.querySelector('.tagline');
    if (taglineEl) {
      taglineEl.style.opacity = '0';
      taglineEl.style.visibility = 'hidden';
    }
    hideLoading();
    // Trigger PixiJS tagline fade
    if (typeof window.fadeTagline === 'function') {
      window.fadeTagline();
    }
    // Play alien onload sound for returning visitors too (loop indefinitely)
    const alienSound = new Audio('/sounds/alien-onload.mp3');
    alienSound.volume = 0.1;
    alienSound.loop = true;
    alienSound.play().catch(() => {});
    // Intensify blob colors
    if (typeof window.intensifyBlob === 'function') {
      window.intensifyBlob();
    }
    if (typeof window.explodeCinders === 'function') {
      window.explodeCinders();
    }
    // Show top nav after a short delay
    setTimeout(() => {
      if (typeof window.showTopNav === 'function') {
        window.showTopNav();
      }
    }, 500);
  }

  // Main sequence
  async function runSequence() {
    cycleStartTime = Date.now();
    currentPhrase = 0;

    // Show loading indicator at start
    showLoading();

    while (currentPhrase < phrases.length) {
      const phrase = phrases[currentPhrase];

      // Type the phrase (use email link for emails)
      if (phrase.isEmail) {
        await typeEmailLink(phrase.text);
      } else {
        await typeText(phrase.text);
      }

      if (phrase.final) {
        // Final phrase - show dots, then fade out
        await new Promise(r => setTimeout(r, PAUSE_AFTER_TYPING));
        await animateDots(DOTS_DURATION);

        hideLoading();

        // Fade out tagline (both HTML and PixiJS versions)
        const taglineEl = document.querySelector('.tagline');
        if (taglineEl) {
          taglineEl.style.transition = 'opacity 1s ease';
          taglineEl.style.opacity = '0';
        }
        // Trigger PixiJS tagline fade - retry if not ready yet
        const tryFadeTagline = () => {
          if (typeof window.fadeTagline === 'function') {
            window.fadeTagline();
          } else {
            setTimeout(tryFadeTagline, 100);
          }
        };
        tryFadeTagline();

        // Wait for fade to complete
        await new Promise(r => setTimeout(r, 1200));

        // Play alien onload sound after fade (loop indefinitely)
        const alienSound = new Audio('/sounds/alien-onload.mp3');
        alienSound.volume = 0.1;
        alienSound.loop = true;
        alienSound.play().catch(err => console.log('Audio error:', err));

        // Intensify blob colors
        if (typeof window.intensifyBlob === 'function') {
          window.intensifyBlob();
        }

        if (typeof window.explodeCinders === 'function') {
          window.explodeCinders();
        }

        // Show top nav after a short delay
        setTimeout(() => {
          if (typeof window.showTopNav === 'function') {
            window.showTopNav();
          }
        }, 500);

        // Mark animation as played for this session
        sessionStorage.setItem('taglineAnimationPlayed', 'true');
      } else if (phrase.dots) {
        // Show animated dots
        await new Promise(r => setTimeout(r, PAUSE_AFTER_TYPING));
        await animateDots(DOTS_DURATION);
        // Delete and move to next
        await deleteText();
      }

      currentPhrase++;
    }

    // Animation complete - don't loop anymore
  }

  // Check if animation already played this session
  if (sessionStorage.getItem('taglineAnimationPlayed')) {
    // Skip to final state
    showFinalState();
  } else {
    // Run the full animation
    runSequence();
  }
});
