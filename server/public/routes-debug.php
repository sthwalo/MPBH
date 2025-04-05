<?php
/**
 * Routes debugging tool
 * Shows all registered routes in Slim Framework
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Create App instance
$app = AppFactory::create();

// Add a simple test route
$app->get('/test', function($request, $response) {
    $response->getBody()->write(json_encode([
        'status' => 'success',
        'message' => 'Route test successful'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Import the actual routes file to see if it loads properly
try {
    $routesFile = require dirname(__DIR__) . '/src/routes.php';
    $routesFile($app);
    
    // Get all routes
    $routes = [];
    $routeCollector = $app->getRouteCollector();
    $routeParser = $routeCollector->getRouteParser();
    foreach ($routeCollector->getRoutes() as $route) {
        $routes[] = [
            'methods' => $route->getMethods(),
            'pattern' => $route->getPattern(),
            'name' => $route->getName() ?: 'unnamed',
        ];
    }
    
    // Output route information
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Routes loaded successfully',
        'count' => count($routes),
        'routes' => $routes
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    // Output error information
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error loading routes: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTrace()
    ], JSON_PRETTY_PRINT);
}
