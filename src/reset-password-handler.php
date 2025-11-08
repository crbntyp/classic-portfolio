<?php
// Start output buffering to prevent any accidental output
ob_start();

session_start();

// Set JSON header first
header('Content-Type: application/json');

// Disable error display (log instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'includes/connection.php';

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get POST data
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid reset token']);
        exit;
    }

    if (empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }

    // Validate token and check expiry
    $stmt = $mysqli->prepare("SELECT userID, username, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($userID, $username, $expiry);

    if ($stmt->fetch()) {
        $stmt->close();

        // Check if token is expired
        if (strtotime($expiry) < time()) {
            echo json_encode(['success' => false, 'message' => 'This reset link has expired. Please request a new one.']);
            exit;
        }

        // Update password and clear reset token
        // Note: In production, you should use password_hash() instead of plain text
        $updateStmt = $mysqli->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE userID = ?");
        $updateStmt->bind_param("si", $password, $userID);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Password reset successful! Redirecting...'
        ]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
    }

    $mysqli->close();

} catch (Exception $e) {
    // Log the error and return a JSON error response
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}

// Flush output buffer
ob_end_flush();
