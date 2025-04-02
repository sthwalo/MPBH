<?php
/**
 * API Debug Tool for Mpumalanga Business Hub
 * This helps identify issues with Slim routes and middleware
 */

header('Content-Type: application/json');

// Basic server information
$serverInfo = [
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'php_version' => PHP_VERSION,
    'request_uri' => $_SERVER['REQUEST_URI'],
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
];

// Check PDO drivers
$pdoDrivers = [
    'available_pdo_drivers' => PDO::getAvailableDrivers(),
    'pgsql_available' => in_array('pgsql', PDO::getAvailableDrivers())
];

// Try to connect to PostgreSQL
$dbConnection = false;
try {
    // Load .env file
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
    
    // Display database configuration (without password)
    $dbConfig = [
        'connection' => $_ENV['DB_CONNECTION'] ?? 'not set',
        'host' => $_ENV['DB_HOST'] ?? 'not set',
        'port' => $_ENV['DB_PORT'] ?? 'not set',
        'database' => $_ENV['DB_NAME'] ?? 'not set',
        'username' => $_ENV['DB_USER'] ?? 'not set'
    ];
    
    // Try connection
    $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query for tables
    $query = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    
    $dbConnection = [
        'status' => 'connected',
        'server_info' => $conn->getAttribute(PDO::ATTR_SERVER_INFO),
        'tables' => $tables
    ];
} catch (Exception $e) {
    $dbConnection = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Check .htaccess rewrite rules
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $modRewriteEnabled = in_array('mod_rewrite', $modules);
} else {
    $modRewriteEnabled = getenv('HTTP_MOD_REWRITE') == 'On' ? true : false;
}

// Verify route files existence
$routeFiles = [
    'routes.php' => file_exists(__DIR__ . '/../src/routes.php'),
    'middleware.php' => file_exists(__DIR__ . '/../src/middleware/middleware.php'),
];

echo json_encode([
    'api_debug' => true,
    'server_info' => $serverInfo,
    'pdo_info' => $pdoDrivers,
    'db_config' => $dbConfig ?? null,
    'db_connection' => $dbConnection,
    'mod_rewrite' => $modRewriteEnabled,
    'route_files' => $routeFiles
], JSON_PRETTY_PRINT);
