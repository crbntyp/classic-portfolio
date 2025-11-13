<?php
// Determine if we're in a subdirectory (e.g., admin/, portfolio/)
// Only set $pathPrefix if not already defined
if (!isset($pathPrefix)) {
    $isSubdir = (basename(dirname($_SERVER['PHP_SELF'])) !== 'portfolio');
    $pathPrefix = $isSubdir ? '../' : '';
}
?>
<?php if (isset($_SESSION['user_id'])): ?>
  <!-- Logged in: Show username, admin link, and logout icon -->
  <div class="user-info">
    <span class="username-display"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
    <?php if (basename(dirname($_SERVER['PHP_SELF'])) === 'admin'): ?>
      <a href="<?php echo $pathPrefix; ?>" class="login-icon" data-tooltip="Home" data-tooltip-position="bottom">
        <i class="las la-otter"></i>
      </a>
    <?php else: ?>
      <a href="<?php echo $pathPrefix; ?>admin/" class="login-icon" data-tooltip="Dashboard" data-tooltip-position="bottom">
        <i class="las la-user-lock"></i>
      </a>
    <?php endif; ?>
    <a href="<?php echo $pathPrefix; ?>logout.php" class="login-icon" data-tooltip="Logout" data-tooltip-position="bottom">
      <i class="las la-sign-out-alt"></i>
    </a>
  </div>
<?php else: ?>
  <!-- Logged out: Show login icon -->
  <a href="#" class="login-icon" id="loginTrigger">
    <i class="las la-sign-in-alt"></i>
  </a>
<?php endif; ?>
