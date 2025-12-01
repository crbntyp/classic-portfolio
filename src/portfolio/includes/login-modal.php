<!-- Login Modal -->
<div class="modal modal--centered" id="loginModal">
  <div class="modal__overlay"></div>
  <div class="modal__content modal__content--narrow">
    <button class="modal__close" id="modalClose">
      <i class="lni lni-close"></i>
    </button>
    <h2 class="modal__logo-text">crbntyp</h2>
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
