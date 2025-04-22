<?php

namespace App\Services\Business;

use App\WebSocket\BusinessNotificationHandler;
use Ratchet\ConnectionInterface;
use App\Exceptions\Exception;

class BusinessWebSocketService
{
    private $notificationHandler;

    public function __construct(BusinessNotificationHandler $notificationHandler)
    {
        $this->notificationHandler = $notificationHandler;
    }

    public function subscribe(ConnectionInterface $conn, int $businessId): void
    {
        if (!$this->notificationHandler->hasConnection($conn)) {
            throw new Exception('Connection not found');
        }

        $this->notificationHandler->subscribe($conn, $businessId);
    }

    public function unsubscribe(ConnectionInterface $conn, int $businessId): void
    {
        if (!$this->notificationHandler->hasConnection($conn)) {
            throw new Exception('Connection not found');
        }

        $this->notificationHandler->unsubscribe($conn, $businessId);
    }

    public function broadcastUpdate(int $businessId, array $data): void
    {
        $this->notificationHandler->broadcast($businessId, [
            'type' => 'update',
            'businessId' => $businessId,
            'data' => $data
        ]);
    }

    public function broadcastNewReview(int $businessId, array $reviewData): void
    {
        $this->notificationHandler->broadcast($businessId, [
            'type' => 'new_review',
            'businessId' => $businessId,
            'review' => $reviewData
        ]);
    }
}
