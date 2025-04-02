<?php
/**
 * Simple Slim Framework test route
 * This helps diagnose route registration issues
 */

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

// Include the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Create App
$app = AppFactory::create();

// Add simple test route
$app->get('/test', function (Request $request, Response $response) {
    $data = [
        'status' => 'success',
        'message' => 'Slim routing is working correctly',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
