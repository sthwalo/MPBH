<?php
require 'vendor/autoload.php';

$rootPath = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

// Display loaded environment variables
echo "Environment Variables:\n";
echo "DB_HOST: " . $_ENV['DB_HOST'] . "\n";
echo "DB_NAME: " . $_ENV['DB_NAME'] . "\n";
echo "DB_USER: " . $_ENV['DB_USER'] . "\n";
echo "DB_CONNECTION: " . $_ENV['DB_CONNECTION'] . "\n\n";

// Test database connection
echo "Testing Database Connection...\n";
try {
    $db = new App\Config\Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!\n";
        echo "Server Info: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    } else {
        echo "❌ Connection failed but no exception thrown\n";
    }
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
