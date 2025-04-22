<?php

namespace App\Services;

use Sentry\SentrySdk;
use Sentry\Integration\TracingIntegration;
use Sentry\Integration\UserFeedbackIntegration;
use Sentry\Tracing\Transaction;
use Sentry\ClientBuilder;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Psr\Log\LoggerInterface;
use App\Exceptions\CustomException;
use App\Models\ErrorLog;
use App\Exceptions\ServiceException;
use PDO;
use Throwable;

class ErrorService
{
    private PDO $db;
    private LoggerInterface $logger;
    private ?Transaction $currentTransaction = null;

    public function __construct(PDO $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        
        try {
            // Initialize Sentry with proper error handling if SENTRY_DSN is set
            if (!empty($_ENV['SENTRY_DSN'])) {
                $clientBuilder = ClientBuilder::create([
                    'dsn' => $_ENV['SENTRY_DSN'],
                    'environment' => $_ENV['APP_ENV'] ?? 'development',
                    'release' => $_ENV['APP_VERSION'] ?? 'unknown',
                    'traces_sample_rate' => 1.0,
                ]);

                // Add integrations
                $clientBuilder->addIntegration(new TracingIntegration());
                $clientBuilder->addIntegration(new UserFeedbackIntegration());

                // Initialize Sentry with client
                SentrySdk::init($clientBuilder->getClient());
                
                $this->logger->info('Sentry initialized successfully');
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to initialize Sentry: ' . $e->getMessage());
            throw new ServiceException('Failed to initialize error service', 0, $e);
        }
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

    /**
     * Log an error with detailed context and optional exception
     * 
     * @param string $message Error message
     * @param array $context Additional context information
     * @param \Exception|null $exception Optional exception to log
     * @throws ServiceException If error logging fails
     */
    public function logError(string $message, array $context = [], ?\Exception $exception = null): void
    {
        try {
            // Add request context if available
            $requestContext = [];
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestContext = [
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'http_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
                ];
            }

            // Combine all context
            $fullContext = array_merge($context, $requestContext);

            // Log to file with detailed context
            $this->logger->error($message, $fullContext);

            // Log to Sentry with enhanced context
            if ($exception) {
                $hub = SentrySdk::getCurrentHub();
                $scope = new Scope();
                $scope->setExtra('context', $fullContext);
                $hub->captureException($exception, $scope);
            } else {
                SentrySdk::getCurrentHub()->captureMessage($message, 'error', $fullContext);
            }

            // Store in database with detailed information
            $this->storeError($message, $fullContext, $exception);

            $this->logger->debug('Error logged successfully', ['message' => $message]);
        } catch (\Exception $e) {
            // Log to file directly if all else fails
            $this->logger->critical('Failed to log error: ' . $e->getMessage(), [
                'original_message' => $message,
                'original_context' => $context,
                'original_exception' => $exception ? $exception->getMessage() : null
            ]);
            throw new ServiceException('Failed to log error', 0, $e);
        }
    }

    private function storeError(string $message, array $context, ?\Exception $exception): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO error_logs (
                    message,
                    context,
                    exception_class,
                    exception_message,
                    exception_trace,
                    request_uri,
                    http_method,
                    remote_ip,
                    user_agent,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $requestContext = [
                'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                'http_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];

            $stmt->execute([
                $message,
                json_encode($context),
                $exception ? get_class($exception) : null,
                $exception ? $exception->getMessage() : null,
                $exception ? $exception->getTraceAsString() : null,
                $requestContext['request_uri'],
                $requestContext['http_method'],
                $requestContext['remote_ip'],
                $requestContext['user_agent']
            ]);

            $this->logger->debug('Error stored in database successfully', ['message' => $message]);
        } catch (\Exception $e) {
            $this->logger->critical('Failed to store error in database: ' . $e->getMessage(), [
                'original_message' => $message,
                'original_context' => $context,
                'original_exception' => $exception ? $exception->getMessage() : null
            ]);
            throw new ServiceException('Failed to store error in database', 0, $e);
        }
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

    /**
     * Handle exceptions in controllers and return an appropriate error response
     * 
     * @param \Exception $exception The exception to handle
     * @param \Psr\Http\Message\ResponseInterface $response The PSR-7 response
     * @param string $context Error context identifier (e.g., 'business.view')
     * @return \Psr\Http\Message\ResponseInterface Modified response with error details
     */
    public function handle(\Exception $exception, $response, string $context): mixed
    {
        // Log the exception with context
        $this->captureException($exception, ['context' => $context]);
        
        // Determine status code based on exception type
        $statusCode = 500;
        if ($exception instanceof \App\Exceptions\NotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof \App\Exceptions\BadRequestException) {
            $statusCode = 400;
        } elseif ($exception instanceof \App\Exceptions\UnauthorizedException) {
            $statusCode = 401;
        } elseif ($exception instanceof \App\Exceptions\ForbiddenException) {
            $statusCode = 403;
        }
        
        // Create error response
        $error = [
            'status' => 'error',
            'message' => $exception->getMessage(),
            'code' => $statusCode,
            'context' => $context
        ];
        
        // In development mode, include more details
        if ($_ENV['APP_ENV'] === 'development') {
            $error['trace'] = $exception->getTraceAsString();
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
        }
        
        // Return JSON response
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
