<?php
/**
 * This router is only for development with PHP's built-in webserver
 */

// Set working directory to document root
chdir(__DIR__);

// The route requested by the browser (minus query parameters)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route static file requests correctly
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico)$/', $uri)) {
    if (file_exists(__DIR__ . $uri)) {
        return false; // Let the webserver handle this directly
    }
}

// Special handling for direct PHP files
if (preg_match('/\.php$/', $uri) && file_exists(__DIR__ . $uri)) {
    return false; // Let the webserver handle this directly
}

// Everything else goes to index.php
require_once __DIR__ . '/index.php';
