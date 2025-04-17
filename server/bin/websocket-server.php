<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

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

// Get port from environment variable or use default
$port = getenv('WEBSOCKET_PORT') ?? 3001;

// Create WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketHandler($logger)
        )
    ),
    $port,
    '0.0.0.0'
);

$logger->info('WebSocket server starting on ws://0.0.0.0:' . $port);

// Run the server
$server->run();