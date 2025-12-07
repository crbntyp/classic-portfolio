<!-- Contact Modal (Start a Project) -->
<div class="modal modal--fullscreen" id="contactModal">
  <div class="modal__overlay modal__overlay--contact">
    <button class="modal__close" id="contactModalClose">close window</button>
    <div class="contact-overlay-content">
      <div class="contact-header">
        <h2 class="contact-title">Start a Project</h2>
        <p class="contact-subtitle">Ready to bring your vision to life? Let's talk.</p>
      </div>

      <form class="contact-form" id="contactForm">
        <div id="contactError" class="error-message" style="display: none;"></div>
        <div id="contactSuccess" class="success-message" style="display: none;"></div>

        <!-- Tier Selection -->
        <div class="tier-selector">
          <label class="tier-selector__label">Select your package</label>
          <div class="tier-selector__options">
            <button type="button" class="tier-option" data-tier="static">
              <span class="tier-option__name">Static</span>
              <span class="tier-option__price">£500</span>
            </button>
            <button type="button" class="tier-option tier-option--selected" data-tier="updateable">
              <span class="tier-option__name">Updateable</span>
              <span class="tier-option__price">£1000</span>
            </button>
            <button type="button" class="tier-option" data-tier="premium">
              <span class="tier-option__name">Premium</span>
              <span class="tier-option__price">£1500</span>
            </button>
            <button type="button" class="tier-option" data-tier="consultation">
              <span class="tier-option__name">Consultation</span>
              <span class="tier-option__price">Your Requirements</span>
            </button>
          </div>
          <input type="hidden" name="tier" id="selectedTier" value="updateable">
        </div>

        <!-- Contact Fields -->
        <div class="form-row">
          <div class="form-group">
            <label for="contactName">Name</label>
            <input type="text" id="contactName" name="name" required>
          </div>
          <div class="form-group">
            <label for="contactEmail">Email</label>
            <input type="email" id="contactEmail" name="email" required>
          </div>
        </div>

        <div class="form-group">
          <label for="contactMessage">Tell me about your project</label>
          <textarea id="contactMessage" name="message" rows="5" required></textarea>
        </div>

        <button type="submit" class="btn-submit">Send Message</button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Tier selection handling
  const tierOptions = document.querySelectorAll('.tier-option');
  const selectedTierInput = document.getElementById('selectedTier');

  tierOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Remove selected from all
      tierOptions.forEach(opt => opt.classList.remove('tier-option--selected'));
      // Add selected to clicked
      this.classList.add('tier-option--selected');
      // Update hidden input
      selectedTierInput.value = this.dataset.tier;
    });
  });

  // Form submission
  const contactForm = document.getElementById('contactForm');
  const errorDiv = document.getElementById('contactError');
  const successDiv = document.getElementById('contactSuccess');

  if (contactForm) {
    contactForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      // Hide previous messages
      errorDiv.style.display = 'none';
      successDiv.style.display = 'none';

      // Get form data
      const formData = new FormData(contactForm);

      // Disable submit button
      const submitBtn = contactForm.querySelector('.btn-submit');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Sending...';
      submitBtn.disabled = true;

      try {
        const response = await fetch('<?php echo $pathPrefix; ?>api/contact-submit.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          successDiv.textContent = result.message;
          successDiv.style.display = 'block';
          contactForm.reset();
          // Reset tier selection to default
          tierOptions.forEach(opt => opt.classList.remove('tier-option--selected'));
          document.querySelector('[data-tier="updateable"]').classList.add('tier-option--selected');
          selectedTierInput.value = 'updateable';
        } else {
          errorDiv.textContent = result.message;
          errorDiv.style.display = 'block';
        }
      } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
      }

      // Re-enable submit button
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
  }
});
</script>
