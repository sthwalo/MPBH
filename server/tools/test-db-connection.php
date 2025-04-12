<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Include the Database class
require_once __DIR__ . '/../src/Config/Database.php';

// Set content type for proper display in browser
header('Content-Type: text/plain');

// Increase error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Afrihost PostgreSQL Database Connection\n\n";

// Display current IP for verification
$currentIp = file_get_contents('https://api.ipify.org');
echo "Current IP Address: " . $currentIp . "\n\n";

echo "Connection Parameters:\n";
echo "Host: {$_ENV['DB_HOST']}\n";
echo "Database: {$_ENV['DB_NAME']}\n";
echo "Port: {$_ENV['DB_PORT']}\n";
echo "User: {$_ENV['DB_USER']}\n";

try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s;connect_timeout=30",
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_NAME']
    );

    echo "\nTesting network connectivity...\n";
    system("ping -c 1 " . $_ENV['DB_HOST']);
    
    echo "\nAttempting database connection without SSL...\n";
    echo "DSN: $dsn\n";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_EMULATE_PREPARES => true
    ];

    try {
        $db = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);
        echo "Connection successful!\n\n";

        // Test query
        $stmt = $db->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "Database version: $version\n";

        // Check if our required tables exist
        echo "\n\nChecking for required tables...\n";
        $tables = array('users', 'businesses');

        foreach ($tables as $table) {
            try {
                // Use a more compatible query that works in both PostgreSQL and MySQL
                $stmt = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '$table'");
                $result = $stmt->fetchColumn();

                echo "Table '$table': " . ($result > 0 ? "Exists" : "Does not exist") . "\n";
            } catch (Exception $e) {
                echo "Error checking table '$table': " . $e->getMessage() . "\n";
            }
        }
    } catch (PDOException $e) {
        echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
        echo "Please verify:\n";
        echo "1. IP {$currentIp} is whitelisted in cPanel\n";
        echo "2. Database user has proper permissions\n";
        echo "3. Database exists and is accessible\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
    echo "Please verify:\n";
    echo "1. IP {$currentIp} is whitelisted in cPanel\n";
    echo "2. Database user has proper permissions\n";
    echo "3. Database exists and is accessible\n";
    exit(1);
}
