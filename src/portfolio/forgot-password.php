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
    require_once 'includes/email-service.php';

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get POST data
    $username = $_POST['username'] ?? '';

    // Validate input
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }

    // Query the database for user with email
    $stmt = $mysqli->prepare("SELECT userID, username, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Use bind_result for compatibility
    $stmt->bind_result($userID, $dbUsername, $userEmail);

    if ($stmt->fetch()) {
        $stmt->close();

        // Check if user has an email address
        if (empty($userEmail)) {
            echo json_encode([
                'success' => false,
                'message' => 'No email address on file. Please contact administrator.'
            ]);
            exit;
        }

        // Generate secure reset token
        $resetToken = bin2hex(random_bytes(32)); // 64 character hex string
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

        // Store token in database
        $updateStmt = $mysqli->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE userID = ?");
        $updateStmt->bind_param("ssi", $resetToken, $expiry, $userID);
        $updateStmt->execute();
        $updateStmt->close();

        // Send reset email
        try {
            $emailService = new EmailService();
            $emailService->sendPasswordReset($userEmail, $dbUsername, $resetToken);

            echo json_encode([
                'success' => true,
                'message' => 'Password reset link has been sent! Check your email.'
            ]);
        } catch (Exception $e) {
            // Log the email error but don't expose it to the user
            error_log("Email sending failed: " . $e->getMessage());

            // Still return success to prevent username enumeration
            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with that username, a reset link has been sent.'
            ]);
        }
    } else {
        $stmt->close();

        // Don't reveal if username exists (security best practice)
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with that username, a reset link has been sent.'
        ]);
    }

    $mysqli->close();

} catch (Exception $e) {
    // Log the error and return a generic JSON error response
    error_log("Forgot password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}

// Flush output buffer
ob_end_flush();
