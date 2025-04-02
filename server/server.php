<?php
/**
 * Custom router for PHP built-in server
 * Designed specifically for Slim Framework
 */

// Determine if the requested file exists as-is
$requestedFile = __DIR__ . '/public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If it exists and is a real file, serve it directly
if (file_exists($requestedFile) && !is_dir($requestedFile) && pathinfo($requestedFile, PATHINFO_EXTENSION) != 'php') {
    // For static files like CSS, JavaScript, images, etc.
    return false;
}

// For everything else, route to front controller
require __DIR__ . '/public/index.php';
