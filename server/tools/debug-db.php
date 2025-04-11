<?php
// Simple script to test PostgreSQL connection

try {
    // Load environment variables
    require __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // Output environment settings
    echo "DB Connection: {$_ENV['DB_CONNECTION']}\n";
    echo "DB Host: {$_ENV['DB_HOST']}\n";
    echo "DB Port: {$_ENV['DB_PORT']}\n";
    echo "DB Name: {$_ENV['DB_NAME']}\n";
    echo "DB User: {$_ENV['DB_USER']}\n";
    
    // Create connection string
    $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";
    
    // Create new PDO instance
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\nConnection successful! Database server info: " . $conn->getAttribute(PDO::ATTR_SERVER_INFO) . "\n";
    
    // Test query to list tables
    $query = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nAvailable tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
