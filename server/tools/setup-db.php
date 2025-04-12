<?php

// Set content type for proper display in browser
header('Content-Type: text/plain');

// Increase error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'mpbusis6k1d8_mpbh',
    'user' => 'mpbusis6k1d8',
    'password' => 'your_local_password'  // Replace with your actual local password
];

try {
    echo "Setting up database with schema...\n";
    
    // Connect to database
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['dbname']
    );
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::ATTR_PERSISTENT => false
    ];
    
    $db = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
    echo "Connected to database successfully!\n\n";
    
    // Drop existing tables
    echo "Dropping existing tables...\n";
    $tables = [
        'analytics_inquiries',
        'analytics_advert_clicks',
        'analytics_product_views',
        'analytics_page_views',
        'payments',
        'adverts',
        'reviews',
        'products',
        'businesses',
        'users'
    ];
    
    foreach ($tables as $table) {
        try {
            $db->exec("DROP TABLE IF EXISTS $table CASCADE");
        } catch (PDOException $e) {
            // Ignore errors if table doesn't exist
            continue;
        }
    }
    echo "Existing tables dropped successfully!\n\n";
    
    // Apply schema
    echo "Applying database schema...\n";
    $schemaFile = __DIR__ . '/../database/clean_schema_pg.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found at: $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    $db->exec($sql);
    echo "Database schema applied successfully!\n\n";
    
    // Apply seed data
    echo "Applying seed data...\n";
    $seedFile = __DIR__ . '/../database/seed_data.sql';
    if (!file_exists($seedFile)) {
        throw new Exception("Seed data file not found at: $seedFile");
    }
    
    $sql = file_get_contents($seedFile);
    $db->exec($sql);
    echo "Seed data applied successfully!\n\n";
    
    // Get list of all tables
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTables created:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
    echo "Your local environment is now ready to test your application.\n";
    
} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Error SQLSTATE: " . $e->errorInfo[0] . "\n";
    echo "\nPlease verify:\n";
    echo "1. PostgreSQL is running on your system\n";
    echo "2. The database exists\n";
    echo "3. The user has proper permissions\n";
    echo "4. The password is correct\n";
    echo "5. The schema file exists and is readable\n";
}
