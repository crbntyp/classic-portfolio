<?php
/**
 * Contact Form Submission API
 * Handles form submissions and stores in database
 */

require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$tier = $_POST['tier'] ?? 'updateable';
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Validate tier
$validTiers = ['static', 'updateable', 'premium'];
if (!in_array($tier, $validTiers)) {
    $tier = 'updateable';
}

// Insert into database
$stmt = $mysqli->prepare("INSERT INTO contact_submissions (name, email, tier, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $name, $email, $tier, $message);

if ($stmt->execute()) {
    // Optionally send email notification here
    // For now, just return success
    echo json_encode(['success' => true, 'message' => 'Message sent successfully! I\'ll be in touch soon.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
}

$stmt->close();
