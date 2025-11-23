<?php
// Determine if we're in a subdirectory (e.g., admin/, portfolio/)
// Only set $pathPrefix if not already defined
if (!isset($pathPrefix)) {
    $isSubdir = (basename(dirname($_SERVER['PHP_SELF'])) !== 'portfolio');
    $pathPrefix = $isSubdir ? '../' : '';
}
?>
<div class="user-info">
  <!-- Social links -->
  <a href="https://github.com/crbntyp" class="user-link" target="_blank">GitHub <i class="lni lni-arrow-angular-top-right"></i></a>
  <a href="https://behance.net/jonny_pyper" class="user-link" target="_blank">Behance <i class="lni lni-arrow-angular-top-right"></i></a>
  <a href="#" class="user-link" id="aboutUsTrigger">Profile</a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logged in: Show admin links -->
    <?php if (basename(dirname($_SERVER['PHP_SELF'])) === 'admin'): ?>
      <a href="#" class="user-link" id="openAddProjectModal">Add project</a>
      <a href="../../" class="user-link">crbntyp home</a>
    <?php else: ?>
      <a href="<?php echo $pathPrefix; ?>admin/" class="user-link">Dashboard </a>
    <?php endif; ?>
    <a href="<?php echo $pathPrefix; ?>logout.php" class="user-link">Logout</a>
  <?php else: ?>
    <!-- Logged out: Show login link -->
    <a href="#" class="user-link" id="loginTrigger">Login</a>
  <?php endif; ?>
</div>
