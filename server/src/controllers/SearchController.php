<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SearchService;
use App\Utils\Sanitizer;

class SearchController {
    private $searchService;
    
    public function __construct($container) {
        $this->searchService = new SearchService($container->get('db'));
    }
    
    /**
     * Handle search requests for businesses
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function handleSearch(Request $request, Response $response): Response {
        try {
            // Get search parameters
            $params = $request->getQueryParams();
            
            // Sanitize inputs
            $query = Sanitizer::cleanInput($params['q'] ?? '');
            $category = Sanitizer::cleanInput($params['category'] ?? '');
            $district = Sanitizer::cleanInput($params['district'] ?? '');
            $page = Sanitizer::cleanInt($params['page'] ?? 1) ?: 1;
            $perPage = Sanitizer::cleanInt($params['per_page'] ?? 10) ?: 10;
            
            // Limit per_page to avoid overloading
            $perPage = min($perPage, 50);
            
            // Perform search
            $results = $this->searchService->searchBusinesses(
                $query,
                [
                    'category' => $category,
                    'district' => $district
                ],
                $page,
                $perPage
            );
            
            // Log search for analytics
            $this->logSearch($request, $query, count($results['data']));
            
            return $response->withJson($results);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get available categories for filtering
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getCategories(Request $request, Response $response): Response {
        try {
            $categories = $this->searchService->getCategories();
            
            return $response->withJson([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Failed to get categories',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get available districts for filtering
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getDistricts(Request $request, Response $response): Response {
        try {
            $districts = $this->searchService->getDistricts();
            
            return $response->withJson([
                'status' => 'success',
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => 'error',
                'message' => 'Failed to get districts',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Log search query for analytics
     * 
     * @param Request $request Request object
     * @param string $query Search query
     * @param int $resultCount Number of results
     */
    private function logSearch(Request $request, string $query, int $resultCount): void {
        try {
            // Get user ID if authenticated
            $userId = null;
            $token = $request->getAttribute('token');
            if ($token) {
                $userId = $token->sub;
            }
            
            // Get IP address
            $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
            
            // Log search to database
            $db = $request->getAttribute('db');
            $stmt = $db->prepare(
                "INSERT INTO search_logs 
                (query, filters, user_id, ip_address, result_count, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            // Get all filters as JSON
            $filters = json_encode(array_filter($request->getQueryParams(), function($key) {
                return $key !== 'q' && $key !== 'page' && $key !== 'per_page';
            }, ARRAY_FILTER_USE_KEY));
            
            $stmt->execute([$query, $filters, $userId, $ip, $resultCount]);
        } catch (\Exception $e) {
            // Just log the error, don't interrupt the main response
            error_log('Failed to log search: ' . $e->getMessage());
        }
    }
}
