<?php
/**
 * Slim Framework Test
 * Basic API endpoints to test PostgreSQL with Slim
 */

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Include the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Create App
$app = AppFactory::create();

// Add middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Add a test route
$app->get('/test', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Test route works!'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add a test business route
$app->get('/businesses', function (Request $request, Response $response) {
    try {
        // Create connection string
        $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}";
        
        // Create new PDO instance
        $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get businesses
        $query = $conn->query("SELECT * FROM businesses LIMIT 10");
        $businesses = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'count' => count($businesses),
            'data' => $businesses
        ]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]));
    }
    
    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
