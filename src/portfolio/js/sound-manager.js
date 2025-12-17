/**
 * Sound Manager
 * Global sound toggle that controls all audio on the site
 * Persists preference in localStorage
 */

(function() {
  // Global sound state - accessible from other scripts
  window.SoundManager = {
    isMuted: false,
    audioElements: [],

    // Check if sound is enabled
    isEnabled: function() {
      return !this.isMuted;
    },

    // Register an audio element to be managed
    register: function(audio) {
      this.audioElements.push(audio);
      if (this.isMuted) {
        audio.muted = true;
      }
    },

    // Mute all registered audio
    muteAll: function() {
      this.isMuted = true;
      this.audioElements.forEach(audio => {
        audio.muted = true;
      });
      localStorage.setItem('crbntyp-sound-muted', 'true');
      this.updateButtons();
    },

    // Unmute all registered audio
    unmuteAll: function() {
      this.isMuted = false;
      this.audioElements.forEach(audio => {
        audio.muted = false;
      });
      localStorage.setItem('crbntyp-sound-muted', 'false');
      this.updateButtons();
    },

    // Toggle mute state
    toggle: function() {
      if (this.isMuted) {
        this.unmuteAll();
      } else {
        this.muteAll();
      }
    },

    // Update all toggle buttons to reflect current state
    updateButtons: function() {
      const buttons = document.querySelectorAll('.sound-toggle');
      buttons.forEach(btn => {
        if (this.isMuted) {
          btn.classList.add('is-muted');
        } else {
          btn.classList.remove('is-muted');
        }
      });
    },

    // Initialize from localStorage
    init: function() {
      const stored = localStorage.getItem('crbntyp-sound-muted');
      this.isMuted = stored === 'true';
      this.updateButtons();
    }
  };

  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize from stored preference
    window.SoundManager.init();

    // Attach click handlers to toggle buttons
    const soundToggle = document.getElementById('soundToggle');
    const mobileSoundToggle = document.getElementById('mobileSoundToggle');

    if (soundToggle) {
      soundToggle.addEventListener('click', function(e) {
        e.preventDefault();
        window.SoundManager.toggle();
      });
    }

    if (mobileSoundToggle) {
      mobileSoundToggle.addEventListener('click', function(e) {
        e.preventDefault();
        window.SoundManager.toggle();
      });
    }
  });
})();
