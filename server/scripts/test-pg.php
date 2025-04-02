<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Override with local PostgreSQL credentials
$_ENV['DB_CONNECTION'] = 'pgsql';
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '5432';
$_ENV['DB_NAME'] = 'mpbh';
$_ENV['DB_USER'] = 'mpbh_user';
$_ENV['DB_PASSWORD'] = 'mpbh_password';

try {
    $dsn = "pgsql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "PostgreSQL connection successful!\n";
    echo "Server version: ";
    print_r($pdo->query("SELECT version()")->fetch()[0]);
    
    // Check the tables
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nAvailable tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}