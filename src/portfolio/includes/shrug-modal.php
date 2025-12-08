<?php
// Fetch shrug entries
$shrugQuery = "SELECT id, title, slug, content, tags, created_at FROM shrug_entries WHERE published = 1 ORDER BY sort_order ASC, created_at DESC";
$shrugResult = $mysqli->query($shrugQuery);
$shrugEntries = [];
$shrugMonths = []; // Track unique months
if ($shrugResult) {
    while ($row = $shrugResult->fetch_assoc()) {
        $shrugEntries[] = $row;
        // Build unique months list (Mon YYYY format for display, YYYY-MM for filtering)
        $monthKey = date('Y-m', strtotime($row['created_at']));
        $monthDisplay = date('M Y', strtotime($row['created_at']));
        if (!isset($shrugMonths[$monthKey])) {
            $shrugMonths[$monthKey] = $monthDisplay;
        }
    }
}
$shrugCount = count($shrugEntries);
// Sort months descending (newest first)
krsort($shrugMonths);
?>
<!-- Shrug Modal -->
<div class="modal modal--fullscreen" id="shrugModal">
  <div class="modal__overlay modal__overlay--shrug">
    <button class="modal__close" id="shrugModalClose">close window</button>
    <div class="shrug-overlay-content">
      <!-- Left side: List -->
      <div class="shrug-list" id="shrugList">
        <!-- Centered Header -->
        <div class="shrug-header">
          <h2 class="shrug-title">Shrug</h2>
          <p class="shrug-subtitle">Thoughts, observations, and things that make me go "meh"</p>

          <?php if (count($shrugMonths) >= 1): ?>
          <!-- Date Filter -->
          <div class="shrug-filter" id="shrugFilter">
            <button class="shrug-filter__btn shrug-filter__btn--active" data-filter="all">All</button>
            <?php foreach ($shrugMonths as $key => $display): ?>
              <button class="shrug-filter__btn" data-filter="<?php echo $key; ?>"><?php echo $display; ?></button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Active Tag Filter Indicator -->
          <div class="shrug-tag-filter" id="shrugTagFilter">
            <span class="shrug-tag-filter__label">Filtering by:</span>
            <span class="shrug-tag-filter__tag" id="shrugActiveTag"></span>
            <button class="shrug-tag-filter__clear" id="shrugClearTag">clear</button>
          </div>
        </div>

        <!-- Masonry Grid -->
        <div class="shrug-entries" id="shrugEntries">
          <?php if ($shrugCount > 0): ?>
            <?php foreach ($shrugEntries as $entry): ?>
              <div class="shrug-entry" data-month="<?php echo date('Y-m', strtotime($entry['created_at'])); ?>" data-slug="<?php echo htmlspecialchars($entry['slug']); ?>" data-tags="<?php echo htmlspecialchars($entry['tags'] ?? ''); ?>" data-full-content="<?php echo htmlspecialchars($entry['content']); ?>">
                <div class="shrug-entry__header">
                  <img src="portfolio/img/prof<?php echo rand(1, 9); ?>.png" alt="" class="shrug-entry__avatar">
                  <div class="shrug-entry__meta">
                    <h3 class="shrug-entry__title"><?php echo htmlspecialchars($entry['title']); ?></h3>
                    <span class="shrug-entry__date"><?php echo date('M j, Y', strtotime($entry['created_at'])); ?> / <span class="shrug-entry__author">Jonny Pyper</span></span>
                  </div>
                </div>
                <div class="shrug-entry__content">
                  <?php
                    // Get content and remove the "shrug..." ending for preview
                    $previewContent = preg_replace('/<p>\s*shrug\.\.\.\s*<\/p>/i', '', $entry['content']);

                    // Truncate to random 100-150 words while preserving HTML
                    $text = strip_tags($previewContent);
                    $words = explode(' ', $text);
                    $truncateAt = rand(100, 150);
                    if (count($words) > $truncateAt) {
                      // Find position of nth word in original HTML
                      $wordCount = 0;
                      $pos = 0;
                      $inTag = false;
                      for ($i = 0; $i < strlen($previewContent); $i++) {
                        if ($previewContent[$i] === '<') $inTag = true;
                        if ($previewContent[$i] === '>') { $inTag = false; continue; }
                        if (!$inTag && $previewContent[$i] === ' ') {
                          $wordCount++;
                          if ($wordCount >= $truncateAt) { $pos = $i; break; }
                        }
                      }
                      if ($pos > 0) {
                        $previewContent = substr($previewContent, 0, $pos) . '... <span class="shrug-read-more">read more →</span></p>';
                      }
                    }
                    echo $previewContent;
                  ?>
                </div>
                <?php if (!empty($entry['tags'])): ?>
                <div class="shrug-entry__tags">
                  <?php foreach (explode(',', $entry['tags']) as $tag): ?>
                    <button class="shrug-tag" data-tag="<?php echo htmlspecialchars(trim($tag)); ?>"><?php echo htmlspecialchars(trim($tag)); ?></button>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="shrug-empty">
              <p>Nothing to shrug about... yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right side: Reader Panel -->
      <div class="shrug-reader" id="shrugReader">
        <button class="shrug-reader__close" id="shrugReaderClose">close shrug</button>
        <div class="shrug-reader__header">
          <img src="" alt="" class="shrug-reader__avatar" id="shrugReaderAvatar">
          <div class="shrug-reader__header-meta">
            <h2 class="shrug-reader__title" id="shrugReaderTitle"></h2>
            <div class="shrug-reader__meta" id="shrugReaderMeta"></div>
          </div>
        </div>
        <div class="shrug-reader__content" id="shrugReaderContent"></div>
        <div class="shrug-reader__tags" id="shrugReaderTags"></div>
      </div>
    </div>
  </div>
</div>
