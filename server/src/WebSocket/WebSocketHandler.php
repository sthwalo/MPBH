<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Monolog\Logger;

class WebSocketHandler implements MessageComponentInterface
{
    protected $clients;
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->clients = new \SplObjectStorage;
        $this->logger = $logger;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->logger->info("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->logger->info("Connection {$from->resourceId} sending message: {$msg}");
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->logger->info("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->logger->error("An error occurred: {$e->getMessage()}");
        $conn->close();
    }
}
