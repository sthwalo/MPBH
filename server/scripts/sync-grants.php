<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $dsn = "pgsql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Get all tables
    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Grant permissions for each table
        $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE $table TO {$_ENV['DB_USER']}";
        $pdo->exec($sql);
        echo "Granted permissions on $table\n";
    }
    
    // Grant sequence permissions
    $sql = "GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO {$_ENV['DB_USER']}";
    $pdo->exec($sql);
    echo "Granted permissions on all sequences\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}