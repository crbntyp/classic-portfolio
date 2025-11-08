<?php
/**
 * Simple .env file loader
 * Loads environment variables from .env file in project root
 */

function loadEnv($path = null) {
    // Default to project root .env file
    if ($path === null) {
        // Try multiple locations for .env file
        $possiblePaths = [
            __DIR__ . '/../../.env',      // Project root (development)
            __DIR__ . '/../.env',          // Dist root (Docker/production)
            __DIR__ . '/.env',             // Same directory
        ];

        $path = null;
        foreach ($possiblePaths as $possiblePath) {
            if (file_exists($possiblePath)) {
                $path = $possiblePath;
                break;
            }
        }

        // If no .env found, return false (will use server env vars)
        if ($path === null) {
            return false;
        }
    }

    // Check if .env file exists
    if (!file_exists($path)) {
        // In production, environment variables should be set by the server
        // So we don't throw an error if .env is missing
        return false;
    }

    // Read the file
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse the line
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);

            // Trim whitespace
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Set the environment variable
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    return true;
}

/**
 * Get an environment variable with optional default value
 */
function env($key, $default = null) {
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    return $value;
}

// Load the .env file automatically when this file is included
loadEnv();
