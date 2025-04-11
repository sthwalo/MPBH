<?php
/**
 * Development server router script
 */

// Set working directory to public directory
chdir(__DIR__ . '/public');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route static file requests directly
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|woff2?|ttf|eot|svg)$/', $uri)) {
    if (file_exists('public' . $uri)) {
        return false;
    }
}

// Handle PHP files directly if they exist
if (preg_match('/\.php$/', $uri) && file_exists('public' . $uri)) {
    return false;
}

// Send everything else to index.php
require_once 'public/index.php';
