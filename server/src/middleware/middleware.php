<?php

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use App\Middleware\JsonBodyParserMiddleware;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();
    
    // Add CORS middleware - IMPORTANT: This must be added BEFORE other middleware
    $app->add(function ($request, $handler) {
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:5173',
            'https://mpbusinesshub.co.za'
        ];
        
        $origin = $request->getHeaderLine('Origin');
        
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            if (in_array($origin, $allowedOrigins)) {
                return $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-CSRF-Token')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Max-Age', '86400');
            }
            return $response->withStatus(200);
        }
        
        // Handle actual request
        $response = $handler->handle($request);
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
    
    // Add remaining middleware...
    $app->add(new JsonBodyParserMiddleware());
    $app->add(new ContentLengthMiddleware());
};
