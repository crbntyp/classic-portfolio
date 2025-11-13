<?php
/**
 * File Upload Handler
 * Centralized file upload validation and processing
 *
 * Usage:
 * $handler = new FileUploadHandler();
 * $filename = $handler->uploadImage($_FILES['projectImage'], 'uploads');
 */

class FileUploadHandler {
    /**
     * Allowed image MIME types
     */
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif'
    ];

    /**
     * Allowed file extensions
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * Maximum file size in bytes (10MB)
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Upload an image file
     *
     * @param array $file The $_FILES array entry for the file
     * @param string $uploadDir The directory to upload to (relative to project root)
     * @param string $prefix Optional filename prefix (default: 'project-')
     * @return string The uploaded filename (not full path)
     * @throws Exception if upload fails
     */
    public function uploadImage($file, $uploadDir = 'uploads', $prefix = 'project-') {
        // Validate file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Validate MIME type
        if (!in_array($file['type'], self::ALLOWED_IMAGE_TYPES)) {
            throw new Exception('Invalid image type. Only JPG, PNG, and GIF allowed.');
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception('Invalid file extension. Only jpg, jpeg, png, and gif allowed.');
        }

        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Generate unique filename
        $filename = $prefix . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image');
        }

        return $filename;
    }

    /**
     * Delete an uploaded image file
     *
     * @param string $filename The filename to delete
     * @param string $uploadDir The directory containing the file
     * @return bool True if deleted successfully, false otherwise
     */
    public function deleteImage($filename, $uploadDir = 'uploads') {
        if (empty($filename)) {
            return false;
        }

        $filepath = $uploadDir . '/' . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Check if a file upload is present
     *
     * @param array $file The $_FILES array entry for the file
     * @return bool True if file is uploaded
     */
    public function hasUpload($file) {
        return isset($file) && $file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Validate image file without uploading
     *
     * @param array $file The $_FILES array entry for the file
     * @return array Array with 'valid' boolean and optional 'error' message
     */
    public function validateImage($file) {
        if (!isset($file)) {
            return ['valid' => false, 'error' => 'No file provided'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => $this->getUploadErrorMessage($file['error'])];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'File size exceeds 10MB limit'];
        }

        if (!in_array($file['type'], self::ALLOWED_IMAGE_TYPES)) {
            return ['valid' => false, 'error' => 'Invalid image type'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'error' => 'Invalid file extension'];
        }

        return ['valid' => true];
    }

    /**
     * Get human-readable error message for upload error code
     *
     * @param int $error The PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds maximum allowed';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}
