<?php
// CORS Middleware - Place this at the top of your API entry point

// Define allowed origins
$allowed_origins = [
    'http://localhost:3000',
    'http://localhost:3001',
    'https://mpbusinesshub.co.za'
];

// Get the origin header
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Check if the origin is allowed
$allowed = in_array($origin, $allowed_origins);

// Set headers if origin is allowed
if ($allowed) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, Accept, Origin, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Expose-Headers: Content-Disposition, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset");
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return 200 OK for preflight requests
    http_response_code(200);
    exit;
}
