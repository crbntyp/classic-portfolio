    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <!-- SortableJS for drag-drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="<?php echo (isset($pathPrefix) ? $pathPrefix : '') . 'js/bundle.js'; ?>"></script>
</body>
</html>
<?php
if (isset($mysqli)) {
    $mysqli->close();
}
?>
