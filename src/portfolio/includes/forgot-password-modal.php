<!-- Forgot Password Modal -->
<div class="modal" id="forgotPasswordModal">
  <div class="modal__overlay"></div>
  <div class="modal__content">
    <button class="modal__close" id="forgotPasswordModalClose">
      <i class="las la-times"></i>
    </button>
    <img src="/logo.png" alt="Carbontype Logo" class="modal__logo" />
    <h2>reset password</h2>
    <form class="login-form" id="forgotPasswordForm">
      <div id="forgotPasswordError" class="error-message" style="display: none;"></div>
      <div id="forgotPasswordSuccess" class="success-message" style="display: none;"></div>
      <div class="form-group">
        <label for="resetUsername">Username</label>
        <input type="text" id="resetUsername" name="username" required>
      </div>
      <button type="submit" class="btn-login">Send Reset Link</button>
      <div style="text-align: center; margin-top: 15px;">
        <a href="#" id="backToLogin" style="color: #0ff; font-size: 12px; text-decoration: none;">Back to Login</a>
      </div>
    </form>
  </div>
</div>
