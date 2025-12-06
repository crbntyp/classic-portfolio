<?php
// Fetch shrug entries
$shrugQuery = "SELECT id, title, content, created_at FROM shrug_entries WHERE published = 1 ORDER BY sort_order ASC, created_at DESC";
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
      <!-- Centered Header -->
      <div class="shrug-header">
        <h2 class="shrug-title">Shrug</h2>
        <p class="shrug-subtitle">Thoughts, observations, and things that make me go "meh"</p>

        <?php if (count($shrugMonths) > 1): ?>
        <!-- Date Filter -->
        <div class="shrug-filter" id="shrugFilter">
          <button class="shrug-filter__btn shrug-filter__btn--active" data-filter="all">All</button>
          <?php foreach ($shrugMonths as $key => $display): ?>
            <button class="shrug-filter__btn" data-filter="<?php echo $key; ?>"><?php echo $display; ?></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Full Width Masonry Grid -->
      <div class="shrug-entries" id="shrugEntries">
        <?php if ($shrugCount > 0): ?>
          <?php foreach ($shrugEntries as $entry): ?>
            <div class="shrug-entry" data-month="<?php echo date('Y-m', strtotime($entry['created_at'])); ?>">
              <div class="shrug-entry__header">
                <h3 class="shrug-entry__title"><?php echo htmlspecialchars($entry['title']); ?></h3>
                <span class="shrug-entry__date"><?php echo date('M j, Y', strtotime($entry['created_at'])); ?> / <span class="shrug-entry__author">Jonny Pyper</span></span>
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
  </div>
</div>
