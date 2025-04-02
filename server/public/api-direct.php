<?php
/**
 * Direct API endpoint for testing
 * This bypasses Slim Framework to test if the server environment is working
 */

header('Content-Type: application/json');

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Attempt direct database connection
try {
    // Create connection string
    $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";
    
    // Create new PDO instance
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get businesses (simple query to test PostgreSQL)
    $stmt = $conn->query("SELECT * FROM businesses LIMIT 10");
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Direct connection successful',
        'count' => count($businesses),
        'data' => $businesses
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $e->getMessage()
    ]);
}
