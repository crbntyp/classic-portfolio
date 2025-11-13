<?php
// Get blob entries for dynamic menu if not already fetched
if (!isset($blobResult)) {
    $blobQuery = "SELECT projectID, projectHeading, url FROM projects WHERE blobEntry = 1 ORDER BY projectID ASC";
    $blobResult = $mysqli->query($blobQuery);
}
?>
<div class="main-nav-container">
  <ul>
    <li>
      <a href="/portfolio/" data-tooltip="Portfolio (mixed)" data-tooltip-position="auto"
        >
      </a>
    </li>
    <li>
      <a href="#" id="aboutUsTrigger" data-tooltip="About" data-tooltip-position="auto"
        ><i class="las la-flask"></i>
      </a>
    </li>
    <li>
      <a href="https://www.behance.net/jonnypyper" data-tooltip="Artwork" data-tooltip-position="auto"
        ><i class="lab la-behance-square"></i>
      </a>
    </li>
    <li class="main-nav">
      <div class="main-nav__popup">
        <?php if ($blobResult && $blobResult->num_rows > 0): ?>
          <?php while ($blobItem = $blobResult->fetch_assoc()): ?>
            <a href="<?php echo htmlspecialchars($blobItem['url']); ?>"
               class="main-nav__item"
               data-tooltip="<?php echo htmlspecialchars($blobItem['projectHeading']); ?>"
               data-tooltip-position="auto">
              <span class="blob-bg"></span>
              <i class="las la-plus-circle"></i>
            </a>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
      <a href="javascript:void(0)" class="main-nav__trigger">
        <i class="las la-rocket"></i>
      </a>
    </li>
    <li>
      <a href="https://github.com/crbntyp" target="_blank" data-tooltip="GitHub" data-tooltip-position="auto">
        <i class="lab la-git-square"></i>
      </a>
    </li>
  </ul>
</div>

<script>
  // Create audio context for blip sounds
  const audioContext = new (window.AudioContext || window.webkitAudioContext)();

  function playBlip() {
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Set blip frequency and type
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(400, audioContext.currentTime + 0.1);

    // Set volume envelope
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

    // Play the blip
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
  }

  document.addEventListener('DOMContentLoaded', function() {
    const blobItems = document.querySelectorAll('.main-nav__item');

    // Detect if touch device
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

    // Track which blob is currently active (showing tooltip)
    let activeBlob = null;

    if (isTouchDevice) {
      // Mobile/touch interaction
      blobItems.forEach(item => {
        item.addEventListener('click', function(e) {
          // If this blob is already active, allow navigation
          if (activeBlob === item) {
            return; // Let the link work normally
          }

          // Otherwise, prevent navigation and show tooltip
          e.preventDefault();

          // Reset previous active blob
          if (activeBlob) {
            activeBlob.classList.remove('tooltip-active');
            activeBlob.removeAttribute('data-tooltip-visible');
          }

          // Set this as active and show tooltip
          activeBlob = item;
          item.classList.add('tooltip-active');
          item.setAttribute('data-tooltip-visible', 'true');

          // Play blip sound
          playBlip();
        });
      });

      // Reset active blob when tapping elsewhere
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.main-nav__item')) {
          if (activeBlob) {
            activeBlob.classList.remove('tooltip-active');
            activeBlob.removeAttribute('data-tooltip-visible');
            activeBlob = null;
          }
        }
      });

    } else {
      // Desktop interaction - hover shows tooltip and plays blip
      blobItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
          // Hide any currently active tooltip
          if (activeBlob && activeBlob !== item) {
            activeBlob.classList.remove('tooltip-active');
            activeBlob.removeAttribute('data-tooltip-visible');
          }

          // Show this tooltip
          item.classList.add('tooltip-active');
          item.setAttribute('data-tooltip-visible', 'true');
          activeBlob = item;

          // Play blip sound
          playBlip();
        });

        item.addEventListener('mouseleave', function() {
          // Hide tooltip on mouse leave
          item.classList.remove('tooltip-active');
          item.removeAttribute('data-tooltip-visible');
          if (activeBlob === item) {
            activeBlob = null;
          }
        });
      });
    }
  });
</script>
