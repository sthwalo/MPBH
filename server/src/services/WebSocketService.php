<?php

namespace App\Services;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\BusinessNotificationHandler;
use App\Exceptions\Exception;

class WebSocketService
{
    private $server;
    private $port;
    private $host;

    public function __construct(string $host = '0.0.0.0', int $port = 8081)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function start(): void
    {
        try {
            $this->server = IoServer::factory(
                new HttpServer(new WsServer(new BusinessNotificationHandler())),
                $this->port,
                $this->host
            );

            $this->server->run();
        } catch (Exception $e) {
            throw new Exception('WebSocket server failed to start: ' . $e->getMessage());
        }
    }

    public function stop(): void
    {
        if ($this->server) {
            $this->server->shutdown();
        }
    }
}
