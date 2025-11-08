<?php
require_once 'includes/init.php';

// Query for blob entries (dynamic menu items)
$blobQuery = "SELECT projectID, projectHeading, url FROM projects WHERE blobEntry = 1 ORDER BY projectID ASC";
$blobResult = $mysqli->query($blobQuery);

// Set page variables
$pageTitle = 'Jonny Pyper | Carbontype | Software Engineering Manager @R7 | Award Winning UI Designer | Crayon Enthusiast';
$bodyClass = 'home-body';
$cssVersion = '11';
?>
<?php include 'includes/html-head.php'; ?>

    <?php include 'includes/user-nav.php'; ?>
    <?php include 'includes/login-modal.php'; ?>
    <?php include 'includes/forgot-password-modal.php'; ?>

    <div class="table">
      <div class="cell">
        <img src="logo.png" class="logo" />
        <h1>crbntyp</h1>
      </div>
    </div>

    <?php include 'includes/main-nav.php'; ?>

<?php include 'includes/html-close.php'; ?>
