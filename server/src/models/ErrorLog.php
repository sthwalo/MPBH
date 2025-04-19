<?php

namespace App\Models;

use PDO;

class ErrorLog
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function logError(string $message, array $context = [], ?\Exception $exception = null, ?int $userId = null, ?string $requestPath = null, ?string $requestMethod = null, ?int $httpStatus = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO error_logs (
                message,
                context,
                exception_class,
                exception_message,
                exception_trace,
                user_id,
                request_path,
                request_method,
                http_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $message,
            json_encode($context),
            $exception ? get_class($exception) : null,
            $exception ? $exception->getMessage() : null,
            $exception ? $exception->getTraceAsString() : null,
            $userId,
            $requestPath,
            $requestMethod,
            $httpStatus
        ]);
    }

    public function getRecentErrors(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM error_logs
            ORDER BY created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getErrorsByType(string $type, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM error_logs
            WHERE exception_class = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$type, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getErrorsByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM error_logs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getErrorStats(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_TRUNC('day', created_at) as date,
                COUNT(*) as total_errors,
                COUNT(DISTINCT user_id) as affected_users
            FROM error_logs
            GROUP BY DATE_TRUNC('day', created_at)
            ORDER BY date DESC
            LIMIT 30
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
