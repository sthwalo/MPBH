<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware that adds security-related HTTP headers to all responses
 */
class SecurityHeadersMiddleware implements MiddlewareInterface {
    /**
     * Process the request through the middleware
     * 
     * @param Request $request The request object
     * @param RequestHandler $handler The request handler
     * @return Response The response with added security headers
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $response = $handler->handle($request);
        
        return $response
            // Helps prevent cross-site scripting (XSS) attacks
            ->withHeader('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://api.mpbusinesshub.co.za")
            
            // Prevents MIME type sniffing
            ->withHeader('X-Content-Type-Options', 'nosniff')
            
            // Protects against clickjacking
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            
            // Enables browser XSS protection
            ->withHeader('X-XSS-Protection', '1; mode=block')
            
            // Controls how much information is sent in the Referer header
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            
            // Controls which features and APIs can be used in the browser
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), interest-cohort=()')
            
            // Enforces HTTPS
            ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }
}
