<!-- Login Modal -->
<div class="modal" id="loginModal">
  <div class="modal__overlay"></div>
  <div class="modal__content">
    <button class="modal__close" id="modalClose">
      <i class="las la-times"></i>
    </button>
    <img src="/portfolio/logo.png" alt="Carbontype Logo" class="modal__logo" />
    <h2>carbontype</h2>
    <form class="login-form" id="loginForm">
      <div id="loginError" class="error-message" style="display: none;"></div>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn-login">Sign In</button>
      <div style="text-align: center; margin-top: 15px;">
        <a href="#" id="forgotPasswordLink" style="color: #0ff; font-size: 12px; text-decoration: none;">Forgot Password?</a>
      </div>
    </form>
  </div>
</div>
