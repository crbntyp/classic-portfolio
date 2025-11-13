<?php
// Disable error display (log instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Load required helpers
require_once 'includes/session-manager.php';
require_once 'includes/api-response.php';

// Initialize secure session
initializeSecureSession();

// Validate request method
ApiResponse::requireMethod('POST');

// Validate required parameters
ApiResponse::requireParams(['username', 'password']);

try {
    require_once 'includes/connection.php';

    // Get POST data (already validated)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query the database
    $stmt = $mysqli->prepare("SELECT userID, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Use bind_result instead of get_result for compatibility
    $stmt->bind_result($userID, $dbUsername, $dbPassword);

    if ($stmt->fetch()) {
        // User found - verify password
        $stmt->close(); // Close statement before setting session

        $isPasswordValid = false;
        $needsRehash = false;

        // Check if password is hashed (bcrypt hashes start with $2y$)
        if (strpos($dbPassword, '$2y$') === 0) {
            // Password is hashed - use password_verify()
            $isPasswordValid = password_verify($password, $dbPassword);

            // Check if password needs rehashing (algorithm or cost changed)
            if ($isPasswordValid && password_needs_rehash($dbPassword, PASSWORD_DEFAULT)) {
                $needsRehash = true;
            }
        } else {
            // Legacy plain text password - compare directly and mark for rehashing
            $isPasswordValid = ($password === $dbPassword);
            if ($isPasswordValid) {
                $needsRehash = true;
            }
        }

        if ($isPasswordValid) {
            // Rehash password if needed (legacy plain text or outdated hash)
            if ($needsRehash) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $mysqli->prepare("UPDATE users SET password = ? WHERE userID = ?");
                $updateStmt->bind_param("si", $newHash, $userID);
                $updateStmt->execute();
                $updateStmt->close();
            }

            // Set session variables
            $_SESSION['user_id'] = $userID;
            $_SESSION['username'] = $dbUsername;

            // Close database connection
            $mysqli->close();

            // Return success response
            ApiResponse::success([
                'username' => $dbUsername
            ], 'Login successful');
        } else {
            $mysqli->close();
            ApiResponse::error('Invalid username or password');
        }
    } else {
        $stmt->close();
        $mysqli->close();
        ApiResponse::error('Invalid username or password');
    }

} catch (Exception $e) {
    // Log the error and return a JSON error response
    error_log("Login error: " . $e->getMessage());
    ApiResponse::serverError('Database connection error. Please try again later.');
}
