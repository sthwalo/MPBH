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
        
        // Business routes (public)
        $group->group('/businesses', function (RouteCollectorProxy $group) {
            // Public routes - list all businesses
            $group->get('', [BusinessController::class, 'getAllBusinesses']);
            
            // Protected routes - require authentication
            // IMPORTANT: Specific static routes come BEFORE variable routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->post('', [BusinessController::class, 'createBusiness']);
                $group->get('/my-business', [BusinessController::class, 'getMyBusiness']);
                $group->put('/my-business', [BusinessController::class, 'updateMyBusiness']);
                $group->post('/my-business/logo', [BusinessController::class, 'uploadLogo']);
                $group->post('/my-business/cover', [BusinessController::class, 'uploadCover']);
            })->add(new AuthMiddleware());
            
            // Variable routes AFTER specific static routes
            $group->get('/{id}', [BusinessController::class, 'getBusinessById']);
            $group->get('/{id}/products', [BusinessController::class, 'getBusinessProducts']);
            $group->get('/{id}/reviews', [BusinessController::class, 'getBusinessReviews']);
        });
        
        // Product routes (protected)
        $group->group('/products', function (RouteCollectorProxy $group) {
            $group->get('', [ProductController::class, 'getMyProducts']);
            $group->post('', [ProductController::class, 'createProduct']);
            $group->get('/{id}', [ProductController::class, 'getProduct']);
            $group->put('/{id}', [ProductController::class, 'updateProduct']);
            $group->delete('/{id}', [ProductController::class, 'deleteProduct']);
            $group->post('/{id}/image', [ProductController::class, 'uploadProductImage']);
        })->add(new AuthMiddleware());
        
        // Review routes
        $group->group('/reviews', function (RouteCollectorProxy $group) {
            // Public route to create review (requires auth but not business ownership)
            $group->post('/business/{id}', [ReviewController::class, 'createReview'])->add(new AuthMiddleware());
            
            // Protected routes
            $group->group('', function (RouteCollectorProxy $group) {
                $group->get('/my-reviews', [ReviewController::class, 'getMyReviews']);
                $group->put('/{id}', [ReviewController::class, 'updateReview']);
                $group->delete('/{id}', [ReviewController::class, 'deleteReview']);
                
                // Admin route for moderation - in a real app we'd have proper admin middleware
                $group->put('/{id}/moderate', [ReviewController::class, 'moderateReview']);
            })->add(new AuthMiddleware());
        });
        
        // Advert routes (protected)
        $group->group('/adverts', function (RouteCollectorProxy $group) {
            $group->get('', [AdvertController::class, 'getMyAdverts']);
            $group->post('', [AdvertController::class, 'createAdvert']);
            $group->get('/{id}', [AdvertController::class, 'getAdvert']);
            $group->put('/{id}', [AdvertController::class, 'updateAdvert']);
            $group->delete('/{id}', [AdvertController::class, 'deleteAdvert']);
            $group->post('/{id}/image', [AdvertController::class, 'uploadImage']);
            $group->get('/public/{placement}', [AdvertController::class, 'getActiveAdverts']);
        })->add(new AuthMiddleware());
        
        // Payment routes (protected)
        $group->group('/payments', function (RouteCollectorProxy $group) {
            $group->get('/history', [PaymentController::class, 'getPaymentHistory']);
            $group->post('/initiate', [PaymentController::class, 'initiatePayment']);
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
    $app->get('/metrics', [SystemController::class, 'metrics'])->add(new AuthMiddleware()); // Protected metrics endpoint
    
    // Redirect root to API documentation or frontend
    $app->get('/', function ($request, $response) {
        return $response
            ->withHeader('Location', $_ENV['FRONTEND_URL'])
            ->withStatus(302);
    });
};
