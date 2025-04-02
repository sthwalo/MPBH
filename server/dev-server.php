<?php
/**
 * Development server router for PHP's built-in server
 * Properly handles both direct PHP files and Slim Framework routes
 */

// Requested URI path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = __DIR__ . '/public';

// If the file exists directly in public directory, serve it
if (file_exists($publicDir . $uri) && !is_dir($publicDir . $uri)) {
    // Direct file access (e.g., api-direct.php, api-debug.php)
    return false; // Let the built-in server handle it directly
}

// Otherwise route to Slim's front controller
require_once $publicDir . '/index.php';
