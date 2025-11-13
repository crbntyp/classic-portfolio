<?php
/**
 * Initialization file
 * Sets up session and database connection
 */

// Initialize secure session
require_once __DIR__ . '/session-manager.php';
initializeSecureSession();

// Load database connection
require_once __DIR__ . '/connection.php';
