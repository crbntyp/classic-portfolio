<?php
// Determine if we're in a subdirectory (e.g., admin/, portfolio/)
// Only set $pathPrefix if not already defined
if (!isset($pathPrefix)) {
    $isSubdir = (basename(dirname($_SERVER['PHP_SELF'])) !== 'portfolio');
    $pathPrefix = $isSubdir ? '../' : '';
}

// Determine active states
$isHomePage = ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php');
$isAdminPage = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
?>
<div class="user-info">
  <!-- Social links -->
  <a href="/" class="user-link<?php echo $isHomePage ? ' is-active' : ''; ?>">crbntyp home</a>
  <a href="https://github.com/crbntyp" class="user-link" target="_blank">GitHub <i class="lni lni-arrow-angular-top-right"></i></a>
  <a href="https://www.behance.net/jonnypyper" class="user-link" target="_blank">Behance <i class="lni lni-arrow-angular-top-right"></i></a>
  <a href="#" class="user-link" id="aboutUsTrigger">Profile</a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logged in: Show admin links -->
    <?php if ($isAdminPage): ?>
      <a href="#" class="user-link is-active" id="openAddProjectModal">Add project</a>
    <?php else: ?>
      <a href="<?php echo $pathPrefix; ?>admin/" class="user-link">Dashboard</a>
    <?php endif; ?>
    <a href="<?php echo $pathPrefix; ?>logout.php" class="user-link">Logout</a>
  <?php else: ?>
    <!-- Logged out: Show login link -->
    <a href="#" class="user-link" id="loginTrigger">Login</a>
  <?php endif; ?>
</div>

<!-- Loading indicator (shown during tagline animation) -->
<div class="loading-indicator" id="loadingIndicator">
  <i class="lni lni-spinner-3"></i>
  <span>loading projects</span>
</div>
