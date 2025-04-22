<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Controllers\AuthController;
use App\Controllers\BusinessController;
use App\Controllers\ProductController;
use App\Controllers\ReviewController;
use App\Controllers\AdvertController;
use App\Controllers\PaymentController;
use App\Controllers\StatisticsController;
use App\Controllers\SearchController;
use App\Controllers\AdminController;
use App\Controllers\SystemController;

return function (App $app) {
    // Apply rate limiting middleware to all API routes
    $app->add(new RateLimitMiddleware(100)); // 100 requests/minute
    
    // API version 1 group
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Auth routes (public)
        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/register', [AuthController::class, 'register']);
            $group->post('/login', [AuthController::class, 'login']);
            $group->post('/logout', [AuthController::class, 'logout'])->add(new AuthMiddleware());
            $group->post('/refresh-token', [AuthController::class, 'refreshToken']);
            $group->post('/forgot-password', [AuthController::class, 'forgotPassword']);
            $group->post('/reset-password', [AuthController::class, 'resetPassword']);
        });
        
        // Search routes (public)
        $group->group('/search', function (RouteCollectorProxy $group) {
            $group->get('', [SearchController::class, 'handleSearch']);
            $group->get('/categories', [SearchController::class, 'getCategories']);
            $group->get('/districts', [SearchController::class, 'getDistricts']);
        });
        
        // Business registration route (public)
        $group->post('/businesses/register', [BusinessController::class, 'registerBusiness']);
        
        // Business routes (public)
        $group->group('/businesses', function (RouteCollectorProxy $group) {
            // Public routes
            $group->get('', [BusinessController::class, 'getAllBusinesses']);
            $group->get('/my', [BusinessController::class, 'getMyBusiness']);
            $group->get('/{id}', [BusinessController::class, 'getById']);
            $group->get('/{id}/stats', [BusinessController::class, 'getStats']);
            $group->get('/{id}/reviews', [BusinessController::class, 'getBusinessReviews']);
            
            // Protected routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->post('', [BusinessController::class, 'create']);
                $group->put('/my', [BusinessController::class, 'updateMyBusiness']);
                $group->put('/{id}', [BusinessController::class, 'update']);
                $group->delete('/{id}', [BusinessController::class, 'delete']);
                $group->post('/{id}/verify', [BusinessController::class, 'verify']);
                $group->post('/{id}/image', [BusinessController::class, 'uploadImage']);
            })->add(new AuthMiddleware());
        });
        
        // Product routes (public)
        $group->group('/products', function (RouteCollectorProxy $group) {
            $group->get('', [ProductController::class, 'getAllProducts']);
            $group->get('/{id}', [ProductController::class, 'getById']);
            $group->get('/{id}/stats', [ProductController::class, 'getStats']);
            
            // Protected routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->post('', [ProductController::class, 'create']);
                $group->put('/{id}', [ProductController::class, 'update']);
                $group->delete('/{id}', [ProductController::class, 'delete']);
                $group->post('/{id}/image', [ProductController::class, 'uploadProductImage']);
            })->add(new AuthMiddleware());
        });
        
        // Advert routes (public)
        $group->group('/adverts', function (RouteCollectorProxy $group) {
            $group->get('', [AdvertController::class, 'getAllAdverts']);
            $group->get('/{id}', [AdvertController::class, 'getById']);
            $group->get('/{id}/stats', [AdvertController::class, 'getStats']);
            
            // Protected routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->post('', [AdvertController::class, 'create']);
                $group->put('/{id}', [AdvertController::class, 'update']);
                $group->delete('/{id}', [AdvertController::class, 'delete']);
                $group->post('/{id}/image', [AdvertController::class, 'uploadImage']);
            })->add(new AuthMiddleware());
        });
        
        // Payment routes (protected)
        $group->group('/payments', function (RouteCollectorProxy $group) {
            $group->post('/initiate', [PaymentController::class, 'initiatePayment']);
            $group->get('/status/{id}', [PaymentController::class, 'getPaymentStatus']);
            $group->get('/packages', [PaymentController::class, 'getPackages']);
        })->add(new AuthMiddleware());
        
        // Statistics routes (protected)
        $group->group('/statistics', function (RouteCollectorProxy $group) {
            $group->get('/dashboard', [StatisticsController::class, 'getDashboardStats']);
            $group->get('/location', [StatisticsController::class, 'getTrafficByLocation']);
            $group->get('/referral', [StatisticsController::class, 'getTrafficByReferral']);
        })->add(new AuthMiddleware());
        
        // Admin routes (protected + admin role)
        $group->group('/admin', function (RouteCollectorProxy $group) {
            $group->get('/businesses/pending', [AdminController::class, 'getPendingBusinesses']);
            $group->put('/businesses/{id}/status', [AdminController::class, 'updateBusinessStatus']);
            $group->get('/dashboard', [AdminController::class, 'getDashboardStats']);
        });
    });
    
    // Exclude webhook endpoints from authentication
    $app->post('/api/payments/notify', [PaymentController::class, 'processWebhook']);
    $app->post('/api/statistics/log/{id}', [StatisticsController::class, 'logInteraction']);
    
    // System endpoints for monitoring and health checks
    $app->get('/health', [SystemController::class, 'healthCheck']);
    $app->get('/metrics', [SystemController::class, 'metrics'])->add(new AuthMiddleware());
    
    // Redirect root to API documentation or frontend
    $app->get('/', function ($request, $response) {
        return $response
            ->withHeader('Location', $_ENV['FRONTEND_URL'])
            ->withStatus(302);
    });
};
