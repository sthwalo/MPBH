<?php

namespace App\Services;

use PDO;
use App\Models\Business;

class SearchService {
    private $db;
    private $business;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->business = new Business($db);
    }
    
    /**
     * Search for businesses based on various criteria
     * 
     * @param array $params Search parameters
     * @return array Businesses matching search criteria
     */
    public function searchBusinesses(array $params): array {
        $query = "SELECT b.* FROM businesses b WHERE b.verification_status = 'verified'";
        $queryParams = [];
        
        // Add search term filter
        if (!empty($params['search'])) {
            $searchTerm = '%' . $params['search'] . '%';
            $query .= " AND (b.name LIKE ? OR b.description LIKE ?)";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        // Add category filter
        if (!empty($params['category'])) {
            $query .= " AND b.category = ?";
            $queryParams[] = $params['category'];
        }
        
        // Add district filter
        if (!empty($params['district'])) {
            $query .= " AND b.district = ?";
            $queryParams[] = $params['district'];
        }
        
        // Add package type filter (for admin queries)
        if (!empty($params['package_type'])) {
            $query .= " AND b.package_type = ?";
            $queryParams[] = $params['package_type'];
        }
        
        // Add geolocation filter if coordinates and radius provided
        if (!empty($params['latitude']) && !empty($params['longitude']) && !empty($params['radius'])) {
            // Calculate distance using Haversine formula
            $query .= " AND (
                6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(b.latitude)) * 
                    cos(radians(b.longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(b.latitude))
                ) <= ?
            )";
            $queryParams[] = $params['latitude'];
            $queryParams[] = $params['longitude'];
            $queryParams[] = $params['latitude'];
            $queryParams[] = $params['radius'];
        }
        
        // Add sorting
        $order = !empty($params['order']) ? strtolower($params['order']) : 'asc';
        $orderDirection = ($order === 'desc') ? 'DESC' : 'ASC';
        
        $sortBy = !empty($params['sort']) ? $params['sort'] : 'name';
        switch ($sortBy) {
            case 'rating':
                // Join with reviews to get average rating
                $query = "SELECT b.*, COALESCE(AVG(r.rating), 0) as average_rating FROM businesses b 
                         LEFT JOIN reviews r ON b.id = r.business_id AND r.status = 'approved' 
                         WHERE b.verification_status = 'verified' ";
                
                // Re-add the filters
                if (!empty($params['search'])) {
                    $query .= " AND (b.name LIKE ? OR b.description LIKE ?)";
                }
                if (!empty($params['category'])) {
                    $query .= " AND b.category = ?";
                }
                if (!empty($params['district'])) {
                    $query .= " AND b.district = ?";
                }
                if (!empty($params['package_type'])) {
                    $query .= " AND b.package_type = ?";
                }
                
                // Group by and order
                $query .= " GROUP BY b.id ORDER BY average_rating $orderDirection";
                break;
                
            case 'newest':
                $query .= " ORDER BY b.created_at $orderDirection";
                break;
                
            case 'name':
            default:
                $query .= " ORDER BY b.name $orderDirection";
        }
        
        // Add pagination
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : 20;
        $offset = ($page - 1) * $limit;
        
        $query .= " LIMIT $limit OFFSET $offset";
        
        // Execute query
        $stmt = $this->db->prepare($query);
        $stmt->execute($queryParams);
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) FROM businesses b WHERE b.verification_status = 'verified'";
        $countParams = [];
        
        if (!empty($params['search'])) {
            $countQuery .= " AND (b.name LIKE ? OR b.description LIKE ?)";
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }
        if (!empty($params['category'])) {
            $countQuery .= " AND b.category = ?";
            $countParams[] = $params['category'];
        }
        if (!empty($params['district'])) {
            $countQuery .= " AND b.district = ?";
            $countParams[] = $params['district'];
        }
        if (!empty($params['package_type'])) {
            $countQuery .= " AND b.package_type = ?";
            $countParams[] = $params['package_type'];
        }
        
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($countParams);
        $totalCount = $countStmt->fetchColumn();
        
        // Calculate metadata for pagination
        $totalPages = ceil($totalCount / $limit);
        
        return [
            'businesses' => $businesses,
            'metadata' => [
                'total' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Get featured businesses for homepage
     * 
     * @param int $limit Number of businesses to return
     * @return array Featured businesses
     */
    public function getFeaturedBusinesses(int $limit = 6): array {
        // Get gold package businesses first, then silver if needed to meet limit
        $query = "SELECT b.* FROM businesses b 
                 WHERE b.verification_status = 'verified' 
                 AND b.package_type = 'Gold' 
                 ORDER BY RAND() LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If we don't have enough gold businesses, add some silver ones
        if (count($businesses) < $limit) {
            $remainingLimit = $limit - count($businesses);
            $query = "SELECT b.* FROM businesses b 
                     WHERE b.verification_status = 'verified' 
                     AND b.package_type = 'Silver' 
                     AND b.id NOT IN (" . implode(',', array_column($businesses, 'id')) . ") 
                     ORDER BY RAND() LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$remainingLimit]);
            $silverBusinesses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $businesses = array_merge($businesses, $silverBusinesses);
        }
        
        return $businesses;
    }
    
    /**
     * Get distinct categories with business counts
     * 
     * @return array Categories with counts
     */
    public function getCategories(): array {
        $query = "SELECT category, COUNT(*) as count 
                 FROM businesses 
                 WHERE verification_status = 'verified' 
                 GROUP BY category 
                 ORDER BY count DESC, category ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get distinct districts with business counts
     * 
     * @return array Districts with counts
     */
    public function getDistricts(): array {
        $query = "SELECT district, COUNT(*) as count 
                 FROM businesses 
                 WHERE verification_status = 'verified' 
                 GROUP BY district 
                 ORDER BY count DESC, district ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
