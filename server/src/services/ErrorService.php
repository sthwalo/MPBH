<?php

namespace App\Services;

use Sentry\SentrySdk;
use Sentry\Integration\TracingIntegration;
use Sentry\Integration\UserFeedbackIntegration;
use Sentry\Tracing\Transaction;
use Psr\Log\LoggerInterface;
use App\Exceptions\CustomException;
use App\Models\ErrorLog;
use PDO;

class ErrorService
{
    private PDO $db;
    private LoggerInterface $logger;
    private ?Transaction $currentTransaction = null;

    public function __construct(PDO $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        
        // Initialize Sentry
        SentrySdk::init([
            'dsn' => $_ENV['SENTRY_DSN'] ?? '',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'release' => $_ENV['APP_VERSION'] ?? 'unknown',
            'traces_sample_rate' => 1.0,
            'integrations' => [
                new TracingIntegration(),
                new UserFeedbackIntegration(),
            ],
        ]);
    }

    public function startTransaction(string $name, string $operation): Transaction
    {
        $this->currentTransaction = SentrySdk::getCurrentHub()->startTransaction([
            'name' => $name,
            'op' => $operation,
        ]);
        return $this->currentTransaction;
    }

    public function finishTransaction(): void
    {
        if ($this->currentTransaction) {
            $this->currentTransaction->finish();
            $this->currentTransaction = null;
        }
    }

    public function logError(string $message, array $context = [], \Exception $exception = null): void
    {
        // Log to file
        $this->logger->error($message, $context);

        // Log to Sentry
        if ($exception) {
            SentrySdk::getCurrentHub()->captureException($exception);
        } else {
            SentrySdk::getCurrentHub()->captureMessage($message, 'error');
        }

        // Store in database
        $this->storeError($message, $context, $exception);
    }

    private function storeError(string $message, array $context, ?\Exception $exception): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO error_logs (
                message,
                context,
                exception_class,
                exception_message,
                exception_trace,
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $message,
            json_encode($context),
            $exception ? get_class($exception) : null,
            $exception ? $exception->getMessage() : null,
            $exception ? $exception->getTraceAsString() : null,
        ]);
    }

    public function captureException(\Exception $exception, array $context = []): void
    {
        $this->logError(
            $exception->getMessage(),
            array_merge($context, [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]),
            $exception
        );
    }

    public function setUserContext(array $user): void
    {
        SentrySdk::getCurrentHub()->setUser($user);
    }

    public function setTags(array $tags): void
    {
        SentrySdk::getCurrentHub()->setTags($tags);
    }

    public function addBreadcrumb(string $message, array $data = [], string $type = 'info'): void
    {
        SentrySdk::getCurrentHub()->addBreadcrumb([
            'message' => $message,
            'data' => $data,
            'type' => $type,
        ]);
    }
}
