<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\ErrorService;
use App\Models\ErrorLog;
use App\Services\ImageService;
use App\Models\Business;
use App\Models\Review;
use App\Models\Product;
use App\Models\Advert;
use App\Services\Business\BusinessService;
use App\Exceptions\CustomException;
use App\Controllers\BusinessController;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\ReviewController;
use App\Controllers\ProductController;
use App\Controllers\AdvertController;
use App\Controllers\ImageController;
use App\Controllers\NotificationController;
use App\Controllers\SubscriptionController;
use App\Controllers\PaymentController;
use App\Controllers\AnalyticsController;

class ErrorMiddleware
{
    private ErrorService $errorService;
    private ErrorLog $errorLog;
    private array $controllers;

    public function __construct(ErrorService $errorService, ErrorLog $errorLog)
    {
        $this->errorService = $errorService;
        $this->errorLog = $errorLog;
        
        // Register all controllers for error handling
        $this->controllers = [
            BusinessController::class,
            UserController::class,
            AuthController::class,
            ReviewController::class,
            ProductController::class,
            AdvertController::class,
            ImageController::class,
            NotificationController::class,
            SubscriptionController::class,
            PaymentController::class,
            AnalyticsController::class
        ];
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            // Start transaction for the request
            $transaction = $this->errorService->startTransaction(
                $request->getUri()->getPath(),
                $request->getMethod()
            );

            // Add request context
            $this->errorService->addBreadcrumb('Request received', [
                'path' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'query' => $request->getQueryParams()
            ]);

            // Add user context if authenticated
            if ($user = $request->getAttribute('user')) {
                $this->errorService->setUserContext([
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username
                ]);
            }

            // Add controller context
            $controller = $request->getAttribute('controller', null);
            if ($controller && in_array($controller, $this->controllers)) {
                $this->errorService->setTags([
                    'controller' => $controller
                ]);
            }

            // Proceed with request
            $response = $handler->handle($request);

            // Add success breadcrumb
            $this->errorService->addBreadcrumb('Request completed successfully', [
                'status' => $response->getStatusCode()
            ]);

            // Finish transaction
            $this->errorService->finishTransaction();

            return $response;

        } catch (\Exception $e) {
            // Log error to database
            $this->errorLog->logError(
                $e->getMessage(),
                [
                    'request' => [
                        'path' => $request->getUri()->getPath(),
                        'method' => $request->getMethod(),
                        'headers' => $request->getHeaders(),
                        'query' => $request->getQueryParams()
                    ],
                    'user' => $request->getAttribute('user') ?? null
                ],
                $e,
                $request->getAttribute('user')?->id ?? null,
                $request->getUri()->getPath(),
                $request->getMethod(),
                $response?->getStatusCode() ?? 500
            );

            // Capture error with Sentry
            $this->errorService->captureException($e, [
                'request' => $request->getQueryParams(),
                'user' => $request->getAttribute('user') ?? null
            ]);

            // Return error response
            return $response->withJson([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
