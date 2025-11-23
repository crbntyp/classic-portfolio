<?php
    // Suppress errors for API endpoints
    error_reporting(0);
    ini_set('display_errors', 0);

    // Load environment variables and helpers
    require_once __DIR__ . '/env-loader.php';
    require_once __DIR__ . '/environment.php';

    // Always use production database (even in Docker for development)
    $hostConfig = env('DB_PROD_HOST', 'localhost');
    $user = env('DB_PROD_USER', '');
    $pass = env('DB_PROD_PASS', '');
    $db = env('DB_PROD_NAME', '');

    // Parse host:port format
    $port = 3306;
    if (strpos($hostConfig, ':') !== false) {
        list($host, $port) = explode(':', $hostConfig);
        $port = (int)$port;
    } else {
        $host = $hostConfig;
    }

    $mysqli = new mysqli($host, $user, $pass, $db, $port);

    // Check connection
    if ($mysqli->connect_error) {
        // For API endpoints, return JSON error instead of throwing
        if (headers_sent() === false && strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }