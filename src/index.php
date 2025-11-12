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
    <?php include 'includes/about-us-modal.php'; ?>

    <div class="table">
      <div class="cell">
        <img src="logo.png" class="logo" />
        <h1>crbntyp</h1>
      </div>
    </div>
    <?php include 'includes/main-nav.php'; ?>

    <audio id="crbntyp-audio" loop>
      <source src="crbntyp-tune.mp3" type="audio/mpeg">
    </audio>

    <div class="audio-controls">
      <button id="playBtn" class="audio-btn" data-tooltip="Play" data-tooltip-position="auto">
        <i class="las la-play"></i>
      </button>
      <button id="pauseBtn" class="audio-btn" style="display: none;" data-tooltip="Pause" data-tooltip-position="auto">
        <i class="las la-pause"></i>
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

<?php include 'includes/html-close.php'; ?>
