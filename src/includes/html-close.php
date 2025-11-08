    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="<?php echo (isset($pathPrefix) ? $pathPrefix : '') . 'js/bundle.js'; ?>"></script>
</body>
</html>
<?php
if (isset($mysqli)) {
    $mysqli->close();
}
?>
