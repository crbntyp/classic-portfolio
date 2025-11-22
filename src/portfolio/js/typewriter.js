/**
 * Typewriter Effect for Tagline
 * Types phrases with animated loading dots between them
 */

document.addEventListener('DOMContentLoaded', function() {
  const textEl = document.querySelector('.tagline-text');
  const dotsEl = document.querySelector('.tagline-dots');
  const cursorEl = document.querySelector('.tagline-cursor');

  if (!textEl || !dotsEl) return;

  const phrases = [
    { text: 'thinking', dots: true },
    { text: 'Engineering Manager', dots: true },
    { text: 'Award winning UI Designer', dots: true },
    { text: 'UI Engineer', dots: true },
    { text: 'People Leader', dots: true },
    { text: 'hello@crbntyp.com', dots: false, final: true, isEmail: true }
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

  // Main sequence
  async function runSequence() {
    cycleStartTime = Date.now();
    currentPhrase = 0;

    while (currentPhrase < phrases.length) {
      const phrase = phrases[currentPhrase];

      // Type the phrase (use email link for emails)
      if (phrase.isEmail) {
        await typeEmailLink(phrase.text);
      } else {
        await typeText(phrase.text);
      }

      if (phrase.final) {
        // Final phrase - hide cursor and explode cinders
        cursorEl.style.display = 'none';
        if (typeof window.explodeCinders === 'function') {
          window.explodeCinders();
        }
      } else if (phrase.dots) {
        // Show animated dots
        await new Promise(r => setTimeout(r, PAUSE_AFTER_TYPING));
        await animateDots(DOTS_DURATION);
        // Delete and move to next
        await deleteText();
      }

      currentPhrase++;
    }

    // Wait for remainder of 1 minute cycle
    const elapsed = Date.now() - cycleStartTime;
    const remaining = Math.max(0, CYCLE_DURATION - elapsed);

    await new Promise(r => setTimeout(r, remaining));

    // Reset and restart
    textEl.textContent = '';
    dotsEl.innerHTML = '';
    cursorEl.style.display = 'inline';
    if (typeof window.resetCinders === 'function') {
      window.resetCinders();
    }
    runSequence();
  }

  // Start the sequence
  runSequence();
});
