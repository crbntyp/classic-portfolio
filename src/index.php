<?php
require_once 'portfolio/includes/init.php';

// Query for all projects (shown as cinders on homepage)
$cindersQuery = "SELECT projectID, projectHeading, url FROM projects ORDER BY sort_order ASC";
$cindersResult = $mysqli->query($cindersQuery);

// Set page variables
$pageTitle = 'Jonny Pyper | Carbontype | Software Engineering Manager @R7 | Award Winning UI Designer | Crayon Enthusiast';
$bodyClass = 'home-body';
$cssVersion = '11';
$pathPrefix = 'portfolio/';
?>
<?php include 'portfolio/includes/html-head.php'; ?>

    <?php include 'portfolio/includes/login-modal.php'; ?>
    <?php include 'portfolio/includes/about-us-modal.php'; ?>
    <?php include 'portfolio/includes/services-modal.php'; ?>
    <?php include 'portfolio/includes/contact-modal.php'; ?>
    <?php include 'portfolio/includes/shrug-modal.php'; ?>

    <!-- Logo - fixed top left, rotated -->
    <a href="/" class="site-logo" id="site-logo">crbntyp</a>

    <!-- Top Navigation - far right (desktop) -->
    <nav class="top-nav" id="top-nav">
      <a href="#work" class="top-nav__link">Featured Work</a>
      <a href="#services" class="top-nav__link">How I can help you</a>
      <a href="#shrug" class="top-nav__link">Shrug</a>
      <a href="#about" class="top-nav__link">What is crbntyp?</a>
      <a href="#contact" class="top-nav__link top-nav__link--button">Start a Project</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/portfolio/admin/" class="top-nav__link top-nav__link--icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 5-5 5 5 0 0 1 5 5"/></svg></a>
      <?php else: ?>
        <a href="#" class="top-nav__link top-nav__link--icon" id="loginTrigger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></a>
      <?php endif; ?>
    </nav>

    <!-- Mobile Navigation -->
    <div class="mobile-header">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/portfolio/admin/" class="mobile-header__lock"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 5-5 5 5 0 0 1 5 5"/></svg></a>
      <?php else: ?>
        <a href="#" class="mobile-header__lock" id="mobileLoginTrigger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></a>
      <?php endif; ?>
      <button class="burger-menu" id="burger-menu" aria-label="Toggle menu">
        <span class="burger-menu__line"></span>
        <span class="burger-menu__line"></span>
      </button>
    </div>

    <div class="mobile-nav" id="mobile-nav">
      <div class="mobile-nav__backdrop" id="mobile-nav-backdrop"></div>
      <div class="mobile-nav__burger-zone"></div>
      <div class="mobile-nav__content">
        <a href="#work" class="mobile-nav__link">Featured Work</a>
        <a href="#services" class="mobile-nav__link">How I can help you</a>
        <a href="#shrug" class="mobile-nav__link">Shrug</a>
        <a href="#about" class="mobile-nav__link">What is crbntyp?</a>
        <a href="#contact" class="mobile-nav__link">Start a Project</a>
      </div>
    </div>

    <!-- Mobile Start a Project button -->
    <a href="#contact" class="mobile-cta">Start a Project</a>

    <!-- Social icons - fixed bottom left, stacked -->
    <div class="site-socials" id="site-socials">
      <a href="https://github.com/jonnypyper" class="site-socials__link" target="_blank"><i class="lni lni-github"></i></a>
      <a href="https://linkedin.com/in/jonnypyper" class="site-socials__link" target="_blank"><i class="lni lni-linkedin"></i></a>
      <a href="https://behance.net/carbontype" class="site-socials__link" target="_blank"><i class="lni lni-behance"></i></a>
    </div>

    <!-- Voice AI Blob Background -->
    <div class="voice-blob-container" id="voice-blob">
        <div class="voice-blob voice-blob--primary"></div>
        <div class="voice-blob voice-blob--secondary"></div>
        <div class="voice-blob voice-blob--tertiary"></div>
        <div class="voice-blob voice-blob--core"></div>
    </div>

    <div class="table">
      <div class="cell">
        <div class="logo-container">
          <h1 class="blob-text">crbntyp</h1>
          <p class="tagline"><span class="tagline-text"></span><span class="tagline-dots"></span></p>
        </div>
      </div>

      

    </div>

    <!-- Cinders container -->
    <div id="cinders-container">
      <?php if ($cindersResult && $cindersResult->num_rows > 0): ?>
        <?php $colors = ['cyan', 'white']; $i = 0; ?>
        <?php while ($cinder = $cindersResult->fetch_assoc()): ?>
          <?php
            $isClassicPortfolio = $cinder['url'] && strpos($cinder['url'], 'carbontype.co/archive') !== false;
            $href = ($cinder['url'] && !$isClassicPortfolio) ? htmlspecialchars($cinder['url']) : 'javascript:void(0)';
          ?>
          <a href="<?php echo $href; ?>"
             class="cinder"
             <?php if ($cinder['url'] && !$isClassicPortfolio): ?>target="_blank"<?php endif; ?>
             data-color="<?php echo $colors[$i % 2]; ?>"
             data-tooltip="<?php echo htmlspecialchars($cinder['projectHeading']); ?>"
             data-tooltip-position="top">
            <span class="cinder-spark"></span>
          </a>
          <?php $i++; ?>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>

    <!-- Audio disabled
    <audio id="crbntyp-audio" loop autoplay>
      <source src="portfolio/crbntyp-tune.mp3" type="audio/mpeg">
    </audio>

    <div class="audio-controls" style="display: none;">
      <button id="playBtn" class="audio-btn" data-tooltip="Play" data-tooltip-position="auto">
        <i class="lni lni-play"></i>
      </button>
      <button id="pauseBtn" class="audio-btn" style="display: none;" data-tooltip="Pause" data-tooltip-position="auto">
        <i class="lni lni-pause"></i>
      </button>
    </div>

    <style>
      .audio-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 10001;
      }

      .audio-btn {
        border: none;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(255,255,255, 0.1);
        border: 1px solid rgba(255,255,255, 0.2);
        border-radius: 5px;
      }

      .audio-btn i {
        font-size: 16px;
        color: #fff;
      }

      .audio-btn:hover {
        transform: scale(1.1);
      }

      .audio-btn:active {
        transform: scale(0.95);
      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const audio = document.getElementById('crbntyp-audio');
        const playBtn = document.getElementById('playBtn');
        const pauseBtn = document.getElementById('pauseBtn');

        function showPlayBtn() {
          playBtn.style.display = 'flex';
          pauseBtn.style.display = 'none';
        }

        function showPauseBtn() {
          playBtn.style.display = 'none';
          pauseBtn.style.display = 'flex';
        }

        playBtn.addEventListener('click', function() {
          audio.play();
          showPauseBtn();
        });

        pauseBtn.addEventListener('click', function() {
          audio.pause();
          showPlayBtn();
        });

        // Listen for audio state changes
        audio.addEventListener('play', showPauseBtn);
        audio.addEventListener('pause', showPlayBtn);
        audio.addEventListener('ended', showPlayBtn);

        // Try to autoplay
        const playPromise = audio.play();

        if (playPromise !== undefined) {
          playPromise.then(() => {
            showPauseBtn();
          }).catch(error => {
            console.log('Autoplay prevented');
            showPlayBtn();
          });
        }
      });
    </script>
    Audio disabled -->

<?php include 'portfolio/includes/html-close.php'; ?>
<!-- Deploy trigger Sun Nov 23 09:46:29 GMT 2025 -->
