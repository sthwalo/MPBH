<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Cache\RedisCache;

/**
 * SystemController handles system-level operations like health checks and metrics
 */
class SystemController {
    private $db;
    
    public function __construct($container) {
        $this->db = $container->get('db');
    }
    
    /**
     * Health Check endpoint that verifies system components
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function healthCheck(Request $request, Response $response): Response {
        return $response->withJson([
            'status' => 'ok',
            'timestamp' => time(),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'db' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'version' => $_ENV['APP_VERSION'] ?? '1.0.0'
        ]);
    }
    
    /**
     * Metrics endpoint for monitoring
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function metrics(Request $request, Response $response): Response {
        $metrics = [
            "# HELP http_requests_total Total number of HTTP requests",
            "# TYPE http_requests_total counter",
            "http_requests_total{path=\"api\"} " . $this->getRequestCount(),
            
            "# HELP db_query_duration_seconds Database query duration in seconds",
            "# TYPE db_query_duration_seconds gauge",
            "db_query_duration_seconds{query=\"business_list\"} " . $this->getQueryStats('business_list'),
            
            "# HELP active_users Current number of active users",
            "# TYPE active_users gauge",
            "active_users " . $this->getActiveUserCount()
        ];
        
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write(implode("\n", $metrics));
        
        return $response;
    }
    
    /**
     * Check database connectivity
     * 
     * @return array Status information about database
     */
    private function checkDatabase(): array {
        try {
            $stmt = $this->db->query("SELECT 1");
            $result = $stmt->fetch();
            
            return [
                'connected' => true,
                'status' => 'ok'
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check Redis connectivity
     * 
     * @return array Status information about Redis
     */
    private function checkRedis(): array {
        try {
            $redis = new RedisCache();
            $testKey = 'health_check_' . time();
            
            // Try to use Redis to see if it's functional
            $testResult = $redis->remember($testKey, 10, function() {
                return true;
            });
            
            $redis->forget($testKey); // Clean up test key
            
            return [
                'connected' => true,
                'status' => 'ok'
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get total request count from logs or cache
     * 
     * @return int Total request count
     */
    private function getRequestCount(): int {
        // This would typically read from a counter in Redis or a log aggregator
        // For this example, we'll return a fixed number
        return 12345;
    }
    
    /**
     * Get database query statistics
     * 
     * @param string $queryType Type of query to get stats for
     * @return float Average query duration in seconds
     */
    private function getQueryStats(string $queryType = 'default'): float {
        // This would typically come from metrics collection
        // For this example, return a fixed value
        $stats = [
            'business_list' => 0.037,
            'user_auth' => 0.022,
            'default' => 0.045
        ];
        
        return $stats[$queryType] ?? $stats['default'];
    }
    
    /**
     * Get active user count
     * 
     * @return int Count of currently active users
     */
    private function getActiveUserCount(): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as count FROM active_sessions WHERE last_activity > ?"
            );
            $stmt->execute([time() - 900]); // Active in last 15 minutes
            $result = $stmt->fetch();
            
            return (int)$result['count'];
        } catch (\Exception $e) {
            // In case of error, return a default value
            return 0;
        }
    }
}
