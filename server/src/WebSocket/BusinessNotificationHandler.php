<?php

namespace App\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Services\Business\BusinessService;
use App\Services\AnalyticsService;
use App\Exceptions\Exception;

class BusinessNotificationHandler implements MessageComponentInterface
{
    private $clients;
    private $businessService;
    private $analyticsService;

    public function __construct(
        BusinessService $businessService,
        AnalyticsService $analyticsService
    ) {
        $this->clients = new \SplObjectStorage;
        $this->businessService = $businessService;
        $this->analyticsService = $analyticsService;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $this->analyticsService->trackConnection($conn);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        $this->analyticsService->trackDisconnection($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->analyticsService->trackError($e);
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $data = json_decode($msg, true);
            
            switch ($data['type']) {
                case 'subscribe':
                    $this->handleSubscription($from, $data);
                    break;
                case 'unsubscribe':
                    $this->handleUnsubscription($from, $data);
                    break;
            }
        } catch (Exception $e) {
            $this->sendError($from, $e->getMessage());
        }
    }

    private function handleSubscription(ConnectionInterface $conn, array $data): void
    {
        $businessId = $data['businessId'] ?? null;
        if (!$businessId) {
            $this->sendError($conn, 'Business ID is required');
            return;
        }

        $this->businessService->subscribe($conn, $businessId);
        $this->sendSuccess($conn, 'Subscribed successfully');
    }

    private function handleUnsubscription(ConnectionInterface $conn, array $data): void
    {
        $businessId = $data['businessId'] ?? null;
        if (!$businessId) {
            $this->sendError($conn, 'Business ID is required');
            return;
        }

        $this->businessService->unsubscribe($conn, $businessId);
        $this->sendSuccess($conn, 'Unsubscribed successfully');
    }

    private function sendSuccess(ConnectionInterface $conn, string $message): void
    {
        $conn->send(json_encode([
            'type' => 'success',
            'message' => $message
        ]));
    }

    private function sendError(ConnectionInterface $conn, string $message): void
    {
        $conn->send(json_encode([
            'type' => 'error',
            'message' => $message
        ]));
    }
}
