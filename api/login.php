<?php
require_once __DIR__ . '/config.php';

/*
 * Logout. session_destroy() on its own leaves the cookie in the browser and the
 * array in memory, so the same request could still read $_SESSION — clear all
 * three, and expire the cookie so the browser stops sending it.
 */
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
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
