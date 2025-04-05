<?php
// Simple script to test PostgreSQL database connection

// Load environment variables
require_once __DIR__ . '/../src/Config/env.php';

// Include the Database class
require_once __DIR__ . '/../src/Config/Database.php';

// Set content type for proper display in browser
header('Content-Type: text/plain');

// Increase error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Afrihost PostgreSQL Database Connection\n\n";
echo "Connection Parameters:\n";
echo "Host: {$_ENV['DB_HOST']}\n";
echo "Database: {$_ENV['DB_NAME']}\n";
echo "Port: {$_ENV['DB_PORT']}\n";
echo "User: {$_ENV['DB_USER']}\n";
echo "Connection String: {$_ENV['DB_CONNECTION']}\n\n";

try {
    // Attempt to connect
    echo "Attempting to connect to Afrihost PostgreSQL database...\n";
    
    $database = new \App\Config\Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "SUCCESS: Connected to Afrihost PostgreSQL database.\n\n";
        
        // Test a query
        echo "Testing simple query...\n";
        try {
            $stmt = $db->query("SELECT current_database(), current_user, version()");
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Database Info:\n";
            print_r($info);
        } catch (Exception $e) {
            echo "Query error: " . $e->getMessage() . "\n";
        }
        
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
    } else {
        echo "ERROR: Failed to connect to Afrihost PostgreSQL database.\n";
    }
} catch (Exception $e) {
    echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
}
