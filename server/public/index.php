<?php
/**
 * Mpumalanga Business Hub API
 * Main entry point for all API requests
 */

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
