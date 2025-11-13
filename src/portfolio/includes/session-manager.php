<?php
/**
 * Session Manager
 *
 * Provides centralized session initialization with secure cookie parameters
 */

require_once __DIR__ . '/environment.php';

/**
 * Initialize a secure session with appropriate cookie parameters
 *
 * This function ensures sessions are configured securely based on the environment.
 * Can be called multiple times safely - will only initialize once.
 *
 * @return bool True if session was started, false if already active
 */
function initializeSecureSession() {
    // Check if session is already started
    if (session_status() === PHP_SESSION_ACTIVE) {
        return false;
    }

    // Configure session cookie parameters before starting session
    session_set_cookie_params([
        'lifetime' => 0,              // Session cookie (expires when browser closes)
        'path' => '/',                // Available across entire domain
        'domain' => '',               // Current domain
        'secure' => isProduction(),   // HTTPS only in production
        'httponly' => true,           // Not accessible via JavaScript (XSS protection)
        'samesite' => 'Lax'          // CSRF protection, allows same-site requests
    ]);

    session_start();

    return true;
}

/**
 * Destroy the current session and clear all session data
 *
 * @return bool True on success
 */
function destroySession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];

        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        return session_destroy();
    }

    return false;
}

/**
 * Regenerate session ID to prevent session fixation attacks
 *
 * @param bool $deleteOldSession Whether to delete the old session file
 * @return bool True on success
 */
function regenerateSessionId($deleteOldSession = true) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return session_regenerate_id($deleteOldSession);
    }

    return false;
}
