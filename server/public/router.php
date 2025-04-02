<?php
/**
 * Router for PHP's built-in server
 * This provides URL rewriting similar to .htaccess for development
 */

// Serve the requested resource as-is if it exists
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI']) && !is_dir(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false; // Let the server handle existing files
}

// Otherwise, route all requests to index.php
require_once __DIR__ . '/index.php';
