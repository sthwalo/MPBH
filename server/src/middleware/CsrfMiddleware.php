<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Exceptions\AuthenticationException;

class CsrfMiddleware implements MiddlewareInterface {
    /**
     * CSRF protection middleware
     * Validates CSRF tokens for POST, PUT, DELETE, and PATCH requests
     * Generates new tokens for GET requests
     * 
     * @param Request $request The request object
     * @param RequestHandler $handler The request handler
     * @return Response
     * @throws AuthenticationException If CSRF token validation fails
     */
    public function process(Request $request, RequestHandler $handler): Response {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $method = $request->getMethod();
        
        // Validate token for state-changing requests
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->getHeaderLine('X-CSRF-Token') ?? 
                    $request->getParsedBody()['csrf_token'] ?? 
                    null;
            
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                throw new AuthenticationException("CSRF token validation failed");
            }
        }
        
        // Generate new token for GET requests
        if ($method === 'GET') {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $handler->handle($request);
    }
}
