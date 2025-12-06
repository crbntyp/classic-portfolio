<?php
// Fetch shrug entries
$shrugQuery = "SELECT id, title, content, created_at FROM shrug_entries WHERE published = 1 ORDER BY sort_order ASC, created_at DESC";
$shrugResult = $mysqli->query($shrugQuery);
$shrugEntries = [];
if ($shrugResult) {
    while ($row = $shrugResult->fetch_assoc()) {
        $shrugEntries[] = $row;
    }
}
$shrugCount = count($shrugEntries);
?>
<!-- Shrug Modal -->
<div class="modal modal--fullscreen" id="shrugModal">
  <div class="modal__overlay modal__overlay--shrug">
    <button class="modal__close" id="shrugModalClose">
      <i class="lni lni-close"></i>
    </button>
    <div class="shrug-overlay-content">
      <div class="shrug-grid<?php echo $shrugCount >= 4 ? ' has-carousel' : ''; ?>">
        <!-- Column 1: Fixed Title -->
        <div class="shrug-column shrug-column--title">
          <h2 class="shrug-title">Shrug</h2>
          <p class="shrug-subtitle">Thoughts, observations, and things that make me go "meh"</p>
        </div>

        <!-- Entries Container -->
        <div class="shrug-entries" id="shrugEntries" data-count="<?php echo $shrugCount; ?>">
          <?php if ($shrugCount > 0): ?>
            <?php foreach ($shrugEntries as $entry): ?>
              <div class="shrug-entry">
                <div class="shrug-entry__header">
                  <h3 class="shrug-entry__title"><?php echo htmlspecialchars($entry['title']); ?></h3>
                  <span class="shrug-entry__date"><?php echo date('M j, Y', strtotime($entry['created_at'])); ?></span>
                </div>
                <div class="shrug-entry__content">
                  <?php echo $entry['content']; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="shrug-empty">
              <p>Nothing to shrug about... yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Carousel navigation (shown when 4+ entries) -->
      <?php if ($shrugCount >= 4): ?>
        <div class="shrug-nav" id="shrugNav">
          <button class="shrug-nav__btn is-disabled" id="shrugNavPrev">
            <i class="lni lni-arrow-left"></i>
          </button>
          <span class="shrug-nav__counter" id="shrugNavCounter">1-3 of <?php echo $shrugCount; ?></span>
          <button class="shrug-nav__btn" id="shrugNavNext">
            <i class="lni lni-arrow-right"></i>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
