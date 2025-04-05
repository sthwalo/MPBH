<?php
/**
 * Mpumalanga Business Hub API
 * Main entry point for all API requests
 */

// CORS headers - added at the very beginning
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, Accept, Origin, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400"); // Cache preflight for 24 hours

// Handle preflight OPTIONS requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

// Set the absolute path to the project root directory
$rootPath = dirname(__DIR__);

// Include the Composer autoloader
require $rootPath . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

// Create Container using PHP-DI
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions($rootPath . '/src/config/container.php');
$container = $containerBuilder->build();

// Create App instance with the container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Error Middleware
$errorMiddleware = require $rootPath . '/src/middleware/error.php';
$errorMiddleware($app);

// Register middleware
require $rootPath . '/src/middleware/middleware.php';

// Register routes - FIXED: Execute the returned function with $app
$routes = require $rootPath . '/src/routes.php';
if (is_callable($routes)) {
    $routes($app);
}

// Run the application
$app->run();
