<!-- Login Modal -->
<div class="modal modal--fullscreen" id="loginModal">
  <div class="modal__overlay modal__overlay--login">
    <button class="modal__close" id="modalClose">close window</button>
    <div class="login-overlay-content">
      <div class="login-header">
        <h2 class="login-title">Sign In</h2>
      </div>
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
      </form>
    </div>
  </div>
</div>
