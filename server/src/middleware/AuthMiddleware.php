<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;

class AuthMiddleware {
    private $jwtSecret;
    private $requestLimits;
    private $cache;
    
    public function __construct() {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        
        // Define rate limits by endpoint type
        $this->requestLimits = [
            'auth' => 10,       // 10 requests per minute for auth endpoints
            'public' => 30,     // 30 requests per minute for public endpoints
            'business' => 100,  // 100 requests per minute for business-related endpoints
            'default' => 60     // 60 requests per minute for all other authenticated endpoints
        ];
        
        // Initialize cache for rate limiting
        // This would ideally be Redis in production
        $this->cache = [];
    }
    
    /**
     * Authentication middleware for protected routes
     * Validates JWT token and adds user data to request
     * 
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     * @return Response
     * @throws AuthenticationException
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
        // Get JWT from Authorization header
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header) || !preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            throw new AuthenticationException('Authentication token required');
        }
        
        $jwt = $matches[1];
        
        try {
            // Decode and validate JWT
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));
            
            // Apply rate limiting
            $this->rateLimit($request, $decoded->sub);
            
            // Start session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Store user ID in session
            $_SESSION['user_id'] = $decoded->sub;
            $_SESSION['email'] = $decoded->email;
            $_SESSION['last_activity'] = time();
            
            // Add decoded token data to request attributes
            $request = $request->withAttribute('token', $decoded);
            $request = $request->withAttribute('user_id', $decoded->sub);
            
            // Check session timeout (30 minutes)
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
                // Session has expired
                session_unset();
                session_destroy();
                throw new AuthenticationException('Session expired');
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
            
            // Process the request
            return $handler->handle($request);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token: ' . $e->getMessage());
        }
    }
    
    /**
     * Admin middleware for admin-only routes
     * Validates if the authenticated user is an admin
     * 
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     * @return Response
     * @throws AuthorizationException
     */
    public function adminOnly(Request $request, RequestHandler $handler): Response {
        // First, authenticate the user
        $response = $this->__invoke($request, $handler);
        
        // Check if user has admin role
        $token = $request->getAttribute('token');
        
        if (!isset($token->role) || $token->role !== 'admin') {
            throw new AuthorizationException('Administrator access required');
        }
        
        return $response;
    }
    
    /**
     * Middleware to check if the user owns the requested business
     * Used for protected business operations
     * 
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     * @return Response
     * @throws AuthorizationException
     */
    public function businessOwnerOnly(Request $request, RequestHandler $handler): Response {
        // First, authenticate the user
        $response = $this->__invoke($request, $handler);
        
        $userId = $request->getAttribute('user_id');
        $businessId = $request->getAttribute('business_id');
        
        // In a real implementation, you'd query the database to check ownership
        // This is a simplified example
        $db = $request->getAttribute('db');
        $stmt = $db->prepare("SELECT * FROM businesses WHERE id = ? AND user_id = ?");
        $stmt->execute([$businessId, $userId]);
        
        if ($stmt->rowCount() === 0) {
            throw new AuthorizationException('You do not have permission to access this business');
        }
        
        return $response;
    }
    
    /**
     * Rate limiting implementation
     * Limits the number of requests per minute based on endpoint type
     * 
     * @param Request $request PSR-7 request
     * @param int $userId User ID for authenticated users
     * @throws AuthenticationException If rate limit is exceeded
     */
    private function rateLimit(Request $request, $userId = null): void {
        // Determine client identifier (IP for unauthenticated, userId for authenticated)
        $clientId = $userId ?? $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        
        // Determine endpoint type for appropriate limit
        $path = $request->getUri()->getPath();
        $endpointType = 'default';
        
        if (strpos($path, '/api/auth') === 0) {
            $endpointType = 'auth';
        } elseif (strpos($path, '/api/businesses') === 0) {
            $endpointType = 'business';
        } elseif (!$userId) {
            $endpointType = 'public';
        }
        
        $limit = $this->requestLimits[$endpointType];
        $cacheKey = "rate_limit:{$clientId}:{$endpointType}";
        
        // Get current minute for time bucketing
        $currentMinute = floor(time() / 60);
        
        // Initialize or get current requests count
        if (!isset($this->cache[$cacheKey]) || $this->cache[$cacheKey]['minute'] !== $currentMinute) {
            $this->cache[$cacheKey] = [
                'minute' => $currentMinute,
                'count' => 1
            ];
        } else {
            // Increment request count for current minute
            $this->cache[$cacheKey]['count']++;
            
            // Check if limit exceeded
            if ($this->cache[$cacheKey]['count'] > $limit) {
                throw new AuthenticationException(
                    "Rate limit exceeded. Please try again later."
                );
            }
        }
    }
}
