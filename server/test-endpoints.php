<?php
/**
 * Mpumalanga Business Hub - API Endpoint Testing Tool
 * 
 * This script tests all critical API endpoints to verify PostgreSQL migration
 */

// Base URL for API
$baseUrl = 'http://localhost:8080';

// Track results
$results = [];
$passCount = 0;
$failCount = 0;

// Define endpoints to test with their methods
$endpoints = [
    // Public endpoints
    ['GET', '/api/businesses', 'List all businesses'],
    ['GET', '/api/search/categories', 'Get all categories'],
    ['GET', '/api/search/districts', 'Get all districts'],
    ['GET', '/', 'API home'],
    
    // Auth endpoints (these will not authenticate but should return 401 instead of errors)
    ['GET', '/api/businesses/my-business', 'Get my business (auth required)'],
    ['GET', '/api/products', 'List products (auth required)'],
];

// Function to make API request
function makeRequest($method, $url, $data = null) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    return [
        'body' => $response ? json_decode($response, true) : null,
        'status' => $statusCode,
        'error' => $error
    ];
}

// Test each endpoint
echo "\n🔍 Testing Mpumalanga Business Hub API Endpoints\n";
echo "=========================================\n";

foreach ($endpoints as $endpoint) {
    [$method, $path, $description] = $endpoint;
    $url = $baseUrl . $path;
    
    echo "\nTesting: {$description}\n";
    echo "URL: {$method} {$url}\n";
    
    $result = makeRequest($method, $url);
    
    // Check if request was successful (200 OK or 401 Unauthorized for auth endpoints)
    $expectedStatuses = [200]; // Default expected status
    if (strpos($description, 'auth required') !== false) {
        $expectedStatuses = [401, 403]; // Auth endpoints should return 401 or 403 without token
    }
    
    $isSuccess = in_array($result['status'], $expectedStatuses) && empty($result['error']);
    
    if ($isSuccess) {
        echo "✅ SUCCESS - Status: {$result['status']}\n";
        $passCount++;
    } else {
        echo "❌ FAILED - Status: {$result['status']}\n";
        if ($result['error']) {
            echo "Error: {$result['error']}\n";
        }
        $failCount++;
    }
    
    // Show response data
    if ($result['body']) {
        echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
    
    // Store result
    $results[] = [
        'endpoint' => "{$method} {$path}",
        'description' => $description,
        'status' => $result['status'],
        'success' => $isSuccess
    ];
}

// Summary
echo "\n\n📊 TEST SUMMARY\n";
echo "===============\n";
echo "Total: " . count($endpoints) . "\n";
echo "Passed: {$passCount}\n";
echo "Failed: {$failCount}\n";
echo "Success Rate: " . round(($passCount / count($endpoints)) * 100) . "%\n";

// Check database connection directly
echo "\n\n📂 DATABASE CONNECTION TEST\n";
echo "==========================\n";

try {
    // Load .env file
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    
    // Connect to PostgreSQL
    $dsn = getenv('DB_CONNECTION') . ':host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME');
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $stmt = $pdo->query("SELECT current_database(), current_user");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ PostgreSQL Connection: SUCCESS\n";
    echo "Database: {$row['current_database']}\n";
    echo "User: {$row['current_user']}\n";
    
    // Check business table
    $stmt = $pdo->query("SELECT COUNT(*) FROM businesses");
    $count = $stmt->fetchColumn();
    
    echo "Total businesses: {$count}\n";
    
} catch (PDOException $e) {
    echo "❌ Database Connection: FAILED\n";
    echo "Error: {$e->getMessage()}\n";
}

echo "\n\n✅ TEST COMPLETED\n";
echo "================\n";
