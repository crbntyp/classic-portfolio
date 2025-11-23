<?php
require_once 'portfolio/includes/init.php';

// Query for non-classic entries (shown as cinders on homepage)
$cindersQuery = "SELECT projectID, projectHeading, url FROM projects WHERE category != 'classic-portfolio' ORDER BY sort_order ASC";
$cindersResult = $mysqli->query($cindersQuery);

// Set page variables
$pageTitle = 'Jonny Pyper | Carbontype | Software Engineering Manager @R7 | Award Winning UI Designer | Crayon Enthusiast';
$bodyClass = 'home-body';
$cssVersion = '11';
$pathPrefix = 'portfolio/';
?>
<?php include 'portfolio/includes/html-head.php'; ?>

    <?php include 'portfolio/includes/user-nav.php'; ?>
    <?php include 'portfolio/includes/login-modal.php'; ?>
    <?php include 'portfolio/includes/about-us-modal.php'; ?>

    <div class="table">
      <div class="cell">
        <div class="logo-container">
          <h1 class="blob-text">crbntyp</h1>
          <p class="tagline"><span class="tagline-text"></span><span class="tagline-dots"></span><span class="tagline-cursor">|</span></p>
        </div>
      </div>

      

    </div>

    <!-- Cinders container -->
    <div id="cinders-container">
      <?php if ($cindersResult && $cindersResult->num_rows > 0): ?>
        <?php $colors = ['white', 'cyan']; $i = 0; ?>
        <?php while ($cinder = $cindersResult->fetch_assoc()): ?>
          <a href="<?php echo htmlspecialchars($cinder['url'] ?: 'portfolio/project.php?id=' . $cinder['projectID']); ?>"
             class="cinder"
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
