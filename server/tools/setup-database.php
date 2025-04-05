<?php
/**
 * Database Setup Script
 * Creates necessary tables for the MPBH application
 */

// Load environment variables
require_once __DIR__ . '/../src/Config/env.php';

// Include the Database class
require_once __DIR__ . '/../src/Config/Database.php';

// Set content type for proper display in browser
header('Content-Type: text/plain');

// Increase error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "MPBH Database Setup Script\n\n";
echo "Environment: {$_ENV['ENVIRONMENT']}\n";
echo "Database: {$_ENV['DB_NAME']}\n\n";

try {
    // Connect to the database
    $database = new \App\Config\Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "Connected to database successfully.\n\n";
    
    // Start transaction
    $db->beginTransaction();
    
    // Check if businesses table exists
    $stmt = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'businesses'");
    $tableExists = ($stmt->fetchColumn() > 0);
    
    if ($tableExists) {
        echo "Businesses table already exists. Skipping creation.\n";
    } else {
        echo "Creating businesses table...\n";
        
        // Create businesses table to match existing users table schema
        $businessesTable = "
        CREATE TABLE IF NOT EXISTS businesses (
            business_id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100) NOT NULL,
            district VARCHAR(100) NOT NULL,
            address TEXT,
            phone VARCHAR(20),
            email VARCHAR(255) NOT NULL,
            website VARCHAR(255),
            package_type VARCHAR(50) DEFAULT 'Basic',
            verified BOOLEAN DEFAULT FALSE,
            active BOOLEAN DEFAULT TRUE,
            rating DECIMAL(3,2) DEFAULT 0,
            views INTEGER DEFAULT 0,
            adverts_remaining INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )";
        
        $db->exec($businessesTable);
        echo "Businesses table created successfully.\n";
    }
    
    // Check if users table exists but has no id field (unlikely scenario)
    $stmt = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users'");
    $usersTableExists = ($stmt->fetchColumn() > 0);
    
    if (!$usersTableExists) {
        echo "Creating users table...\n";
        
        // Create users table based on User.php model
        $usersTable = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            reset_token VARCHAR(255),
            reset_token_expires TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->exec($usersTable);
        echo "Users table created successfully.\n";
    } else {
        echo "Users table already exists. Skipping creation.\n";
    }
    
    // Commit transaction
    $db->commit();
    
    echo "\nDatabase setup completed successfully.\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    
    echo "ERROR: " . $e->getMessage() . "\n";
}
