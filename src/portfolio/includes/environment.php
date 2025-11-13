<?php
/**
 * Environment Detection Utilities
 *
 * Provides helper functions to detect the current environment
 * (production, development, Docker, etc.)
 */

/**
 * Check if the application is running in production environment
 *
 * @return bool True if in production, false otherwise
 */
function isProduction() {
    return !file_exists('/.dockerenv') &&
           strpos($_SERVER['HTTP_HOST'], 'localhost') === false;
}

/**
 * Check if the application is running in Docker container
 *
 * @return bool True if in Docker, false otherwise
 */
function isDocker() {
    return file_exists('/.dockerenv');
}

/**
 * Check if the application is running in development/local environment
 *
 * @return bool True if in development, false otherwise
 */
function isDevelopment() {
    return !isProduction();
}

/**
 * Get the current environment name as a string
 *
 * @return string 'production', 'docker', or 'development'
 */
function getEnvironment() {
    if (isDocker()) {
        return 'docker';
    }

    if (isProduction()) {
        return 'production';
    }

    return 'development';
}
