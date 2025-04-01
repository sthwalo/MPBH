<?php

use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response;

return function (App $app) {
    $errorMiddleware = $app->addErrorMiddleware(
        $_ENV['LOG_LEVEL'] === 'debug',
        true,
        true,
        $app->getContainer()->get(\Monolog\Logger::class)
    );
    
    $errorMiddleware->setDefaultErrorHandler(function (
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app) {
        $logger = $app->getContainer()->get(\Monolog\Logger::class);
        
        if ($logErrors) {
            $logger->error($exception->getMessage(), [
                'exception' => $exception,
                'url' => (string)$request->getUri(),
                'method' => $request->getMethod(),
            ]);
        }
        
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        
        // Determine the HTTP status code
        $statusCode = 500;
        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof \App\Exceptions\AuthenticationException) {
            $statusCode = 401;
        } elseif ($exception instanceof \App\Exceptions\AuthorizationException) {
            $statusCode = 403;
        } elseif ($exception instanceof \App\Exceptions\ValidationException) {
            $statusCode = 422;
        } elseif ($exception instanceof \App\Exceptions\NotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof \App\Exceptions\BadRequestException) {
            $statusCode = 400;
        }
        
        $response = $response->withStatus($statusCode);
        
        $responseData = [
            'status' => 'error',
            'code' => $statusCode,
            'message' => $exception->getMessage(),
        ];
        
        // Add validation errors if available
        if ($exception instanceof \App\Exceptions\ValidationException && !empty($exception->getErrors())) {
            $responseData['errors'] = $exception->getErrors();
        }
        
        // Add debug information if enabled
        if ($displayErrorDetails) {
            $responseData['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }
        
        $response->getBody()->write(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        return $response;
    });
    
    return $errorMiddleware;
};
