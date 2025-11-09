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
        ><i class="las la-record-vinyl"></i>
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
