<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;
use App\Utils\Sanitizer;
use PDO;
use Monolog\Logger;

class SearchController {
    private $db;
    private $logger;
    
    public function __construct(PDO $db, Logger $logger) {
        $this->db = $db;
        $this->logger = $logger;
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
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            
            // Build query
            $sql = "SELECT b.*, c.name AS category_name, d.name AS district_name, 
                    (SELECT COUNT(*) FROM reviews r WHERE r.business_id = b.id) AS review_count,
                    (SELECT COALESCE(AVG(rating), 0) FROM reviews r WHERE r.business_id = b.id) AS average_rating 
                    FROM businesses b 
                    LEFT JOIN categories c ON b.category_id = c.id 
                    LEFT JOIN districts d ON b.district_id = d.id 
                    WHERE b.status = 'approved'";
            
            $params = [];
            
            // Add search conditions
            if (!empty($query)) {
                $sql .= " AND (b.name ILIKE :query OR b.description ILIKE :query OR b.tags ILIKE :query)";
                $params['query'] = "%{$query}%";
            }
            
            if (!empty($category)) {
                $sql .= " AND b.category_id = :category";
                $params['category'] = $category;
            }
            
            if (!empty($district)) {
                $sql .= " AND b.district_id = :district";
                $params['district'] = $district;
            }
            
            // Add order by and pagination
            $sql .= " ORDER BY b.name ASC LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = ($page - 1) * $limit;
            
            // Execute query
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Count total results for pagination
            $countSql = "SELECT COUNT(*) FROM businesses b WHERE b.status = 'approved'";
            $countParams = [];
            
            if (!empty($query)) {
                $countSql .= " AND (b.name ILIKE :query OR b.description ILIKE :query OR b.tags ILIKE :query)";
                $countParams['query'] = "%{$query}%";
            }
            
            if (!empty($category)) {
                $countSql .= " AND b.category_id = :category";
                $countParams['category'] = $category;
            }
            
            if (!empty($district)) {
                $countSql .= " AND b.district_id = :district";
                $countParams['district'] = $district;
            }
            
            $countStmt = $this->db->prepare($countSql);
            foreach ($countParams as $key => $value) {
                $countStmt->bindValue(":$key", $value);
            }
            $countStmt->execute();
            $totalCount = $countStmt->fetchColumn();
            
            // Return results
            $result = [
                'businesses' => $businesses,
                'pagination' => [
                    'total' => (int)$totalCount,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($totalCount / $limit)
                ]
            ];
            
            return ResponseHelper::success($response, $result);
            
        } catch (\Exception $e) {
            $this->logger->error("Search error: " . $e->getMessage());
            return ResponseHelper::error($response, "Error processing search", 500);
        }
    }
    
    /**
     * Get all business categories
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getCategories(Request $request, Response $response): Response {
        try {
            $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ResponseHelper::success($response, $categories);
            
        } catch (\Exception $e) {
            $this->logger->error("Categories error: " . $e->getMessage());
            return ResponseHelper::error($response, "Error fetching categories", 500);
        }
    }
    
    /**
     * Get all districts
     * 
     * @param Request $request The request object
     * @param Response $response The response object
     * @return Response
     */
    public function getDistricts(Request $request, Response $response): Response {
        try {
            $stmt = $this->db->query("SELECT * FROM districts ORDER BY name ASC");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ResponseHelper::success($response, $districts);
            
        } catch (\Exception $e) {
            $this->logger->error("Districts error: " . $e->getMessage());
            return ResponseHelper::error($response, "Error fetching districts", 500);
        }
    }
}
