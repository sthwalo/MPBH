<?php

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define path constants
define('ROOT_PATH', dirname(__DIR__));

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
$config = require ROOT_PATH . '/src/config/config.php';

// Create container
$container = require ROOT_PATH . '/src/config/container.php';

// Create app with container
$app = new Slim\App($container);

// Register routes
require ROOT_PATH . '/src/routes.php';

// Run app
$app->run();
