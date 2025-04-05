<?php
// Set CORS headers to allow ALL requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the raw POST data
$rawData = file_get_contents('php://input');

// Parse the JSON
$jsonData = json_decode($rawData, true);

// Special case: If this is an OPTIONS request, return 200 OK immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// For any auth-related endpoint or registration request, return a successful response
if (isset($_GET['action']) || strpos($_SERVER['REQUEST_URI'], 'auth/register') !== false) {
    // Log the registration attempt
    file_put_contents('register_attempt.log', date('Y-m-d H:i:s') . ' - ' . $rawData . "\n", FILE_APPEND);
    
    // Create a mock successful response for testing purposes
    $response = [
        'status' => 'success',
        'message' => 'Registration successful',
        'data' => [
            'user_id' => 123,
            'business_id' => 456,
            'token' => 'mock_token_' . time()
        ]
    ];
    
    echo json_encode($response);
    exit();
}

// Default debug information response
$response = [
    'status' => 'debug',
    'message' => 'Request received',
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not specified',
    'raw_data' => $rawData,
    'json_parse_success' => json_last_error() === JSON_ERROR_NONE,
    'json_parse_error' => json_last_error_msg(),
    'parsed_data' => $jsonData,
    'headers' => getallheaders()
];

echo json_encode($response, JSON_PRETTY_PRINT);
