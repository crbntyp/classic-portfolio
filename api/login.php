<?php
require_once __DIR__ . '/config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    jsonResponse(['success' => true]);
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
    jsonResponse(['success' => false, 'message' => 'Username and password required'], 400);
}

$stmt = $mysqli->prepare('SELECT userID as id, username, password FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    // Handle legacy plaintext passwords
    if ($user && $user['password'] === $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $mysqli->prepare('UPDATE users SET password = ? WHERE userID = ?');
        $update->bind_param('si', $hash, $user['id']);
        $update->execute();
        $update->close();
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
    }
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

jsonResponse(['success' => true, 'username' => $user['username']]);
