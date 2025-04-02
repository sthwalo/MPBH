<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Cache\RedisCache;

class RateLimitMiddleware implements MiddlewareInterface {
    private $redis;
    private $enabled;
    private $requestsPerMinute;
    
    /**
     * Initialize rate limiting middleware
     */
    public function __construct() {
        $this->redis = new RedisCache();
        $this->enabled = !empty($_ENV['RATE_LIMIT_ENABLED']) && $_ENV['RATE_LIMIT_ENABLED'] === 'true';
        $this->requestsPerMinute = !empty($_ENV['RATE_LIMIT_REQUESTS']) ? (int)$_ENV['RATE_LIMIT_REQUESTS'] : 60;
    }
    
    /**
     * Process the request through rate limiting middleware
     * 
     * @param Request $request The request object
     * @param RequestHandler $handler The request handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response {
        // Skip rate limiting if disabled
        if (!$this->enabled) {
            return $handler->handle($request);
        }
        
        // Get client IP address
        $clientIp = $this->getClientIp($request);
        $key = "rate_limit:{$clientIp}";
        
        // Use Redis to track requests
        $currentRequests = $this->redis->remember($key, 60, function() {
            return 0; // Initialize counter
        });
        
        // Increment the counter
        $currentRequests++;
        $this->redis->forget($key); // Delete old key
        $this->redis->remember($key, 60, function() use ($currentRequests) {
            return $currentRequests;
        });
        
        // Set rate limit headers
        $response = $handler->handle($request);
        return $response
            ->withHeader('X-RateLimit-Limit', (string)$this->requestsPerMinute)
            ->withHeader('X-RateLimit-Remaining', (string)max(0, $this->requestsPerMinute - $currentRequests))
            ->withHeader('X-RateLimit-Reset', (string)(time() + 60));
    }
    
    /**
     * Get the client IP address from the request
     * Handles proxy servers and various header configurations
     * 
     * @param Request $request
     * @return string IP address
     */
    private function getClientIp(Request $request): string {
        $serverParams = $request->getServerParams();
        
        // Check for proxy headers first
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED'
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ips = explode(',', $serverParams[$header]);
                return trim($ips[0]); // Return the first IP in the list
            }
        }
        
        // Fall back to REMOTE_ADDR
        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
