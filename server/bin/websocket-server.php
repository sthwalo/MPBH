<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\WebSocketHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Create logger
$logger = new Logger('websocket');
$logger->pushHandler(new StreamHandler(dirname(__DIR__) . '/logs/websocket.log', Logger::DEBUG));

// Create WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketHandler($logger)
        )
    ),
    3001, // Changed from 3000 to 3001
    '0.0.0.0' // Listen on all interfaces
);

$logger->info('WebSocket server starting on ws://' . '0.0.0.0' . ':' . 3001);

// Run the server
$server->run();