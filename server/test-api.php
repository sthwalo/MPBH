<?php
// API Test Suite for Mpumalanga Business Hub

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a simple HTTP client
function httpRequest($method, $endpoint, $data = null, $token = null) {
    $url = "http://localhost:8080{$endpoint}";
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } else if ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $status,
        'response' => $response ? json_decode($response, true) : null,
        'error' => $error
    ];
}

// Test reporting
function reportTest($name, $result, $details = null) {
    echo "\n📋 Testing: {$name}\n";
    echo "Status: {$result['status']}\n";
    
    if ($result['error']) {
        echo "Error: {$result['error']}\n";
    }
    
    if ($details) {
        echo "Details: {$details}\n";
    }
    
    if ($result['status'] >= 200 && $result['status'] < 300) {
        echo "✅ SUCCESS\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    return $result['status'] >= 200 && $result['status'] < 300;
}

echo "\n==================================================\n";
echo "🧪 MPUMALANGA BUSINESS HUB API TEST SUITE\n";
echo "==================================================\n";

// 1. Test public endpoints
echo "\n📁 TESTING PUBLIC ENDPOINTS\n";

// 1.1 Test businesses listing
$businessResult = httpRequest('GET', '/api/businesses');
reportTest('Get Businesses Listing', $businessResult, 
    isset($businessResult['response']['data']) ? "Found " . count($businessResult['response']['data']) . " businesses" : "No businesses found");

// 1.2 Test categories listing
$categoriesResult = httpRequest('GET', '/api/search/categories');
reportTest('Get Categories', $categoriesResult);

// 1.3 Test districts listing
$districtsResult = httpRequest('GET', '/api/search/districts');
reportTest('Get Districts', $districtsResult);

// 2. Test authentication
echo "\n📁 TESTING AUTHENTICATION\n";

// 2.1 Test user registration
$testEmail = 'test_' . time() . '@example.com';
$testPassword = 'Test123!';

$registerData = [
    'email' => $testEmail,
    'password' => $testPassword,
    'confirmPassword' => $testPassword,
    'businessName' => 'Test Business',
    'category' => 'Tourism',
    'district' => 'Mbombela',
    'description' => 'This is a test business created by the API test suite. It should be automatically removed after testing.'
];

$registerResult = httpRequest('POST', '/api/auth/register', $registerData);
reportTest('User Registration', $registerResult);

// 2.2 Test login
if ($registerResult['status'] === 201 || $registerResult['status'] === 200) {
    $loginData = [
        'email' => $testEmail,
        'password' => $testPassword
    ];
    
    $loginResult = httpRequest('POST', '/api/auth/login', $loginData);
    $success = reportTest('User Login', $loginResult);
    
    if ($success && isset($loginResult['response']['token'])) {
        $token = $loginResult['response']['token'];
        
        // 3. Test authenticated endpoints
        echo "\n📁 TESTING AUTHENTICATED ENDPOINTS\n";
        
        // 3.1 Test get user profile
        $profileResult = httpRequest('GET', '/api/auth/profile', null, $token);
        reportTest('Get User Profile', $profileResult);
        
        // Get the business ID for further testing
        if (isset($profileResult['response']['business']['id'])) {
            $businessId = $profileResult['response']['business']['id'];
            
            // 3.2 Test creating a product
            $productData = [
                'name' => 'Test Product',
                'description' => 'Test product description',
                'price' => 100.50
            ];
            
            $productResult = httpRequest('POST', "/api/businesses/{$businessId}/products", $productData, $token);
            reportTest('Create Product', $productResult);
            
            // 3.3 Test getting business products
            $productsResult = httpRequest('GET', "/api/businesses/{$businessId}/products", null, $token);
            reportTest('Get Business Products', $productsResult);
        }
    }
}

echo "\n==================================================\n";
echo "🧪 TEST SUITE COMPLETED\n";
echo "==================================================\n";
