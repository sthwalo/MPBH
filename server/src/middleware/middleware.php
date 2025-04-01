<?php

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Content-Length header to the response
    $app->add(new ContentLengthMiddleware());
    
    // Add CORS middleware
    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        
        // Get allowed origins from environment or use defaults
        $allowedOrigins = isset($_ENV['ALLOWED_ORIGINS']) 
            ? explode(',', $_ENV['ALLOWED_ORIGINS']) 
            : [$_ENV['FRONTEND_URL']];
            
        // Add localhost for development environment
        if ($_ENV['APP_ENV'] === 'development') {
            $allowedOrigins[] = 'http://localhost:5173';
            $allowedOrigins[] = 'http://localhost:3000';
        }
        
        // Get the origin from the request
        $origin = $request->getHeaderLine('Origin');
        
        // Set CORS headers if the origin is allowed
        if (in_array($origin, $allowedOrigins)) {
            return $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    });
    
    // Handle pre-flight OPTIONS requests
    $app->options('/{routes:.+}', function ($request, $response) {
        return $response;
    });
};
