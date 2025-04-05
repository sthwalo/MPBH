<?php

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use App\Middleware\JsonBodyParserMiddleware;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();
    
    // Add our custom JSON body parser middleware
    $app->add(new JsonBodyParserMiddleware());

    // Add the Content-Length header to the response
    $app->add(new ContentLengthMiddleware());
    
    // Add CORS middleware - IMPORTANT: This middleware must be added BEFORE route handlers
    $app->add(function ($request, $handler) {
        // Get allowed origins from environment or use defaults - include ALL development origins
        $allowedOrigins = [
            'http://localhost:3000',  // React default
            'http://localhost:3001',  // Alternative React port
            'http://localhost:5173',  // Vite default
            'https://mpbusinesshub.co.za' // Production
        ];
        
        // Handle preflight OPTIONS requests immediately
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            $origin = $request->getHeaderLine('Origin');
            
            if (in_array($origin, $allowedOrigins)) {
                return $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-CSRF-Token')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
            }
            
            return $response->withStatus(200);
        }
        
        // For non-OPTIONS requests, process the request then add CORS headers to the response
        $response = $handler->handle($request);
        $origin = $request->getHeaderLine('Origin');
        
        if (in_array($origin, $allowedOrigins)) {
            return $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-CSRF-Token')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        }
        
        return $response;
    });
};
