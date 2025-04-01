<?php

namespace App\Models;

use PDO;

class Statistic
{
    private PDO $db;
    
    /**
     * Constructor with database dependency
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get dashboard statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Statistics data
     */
    public function getDashboardStatistics(int $businessId): array
    {
        return [
            'visitors' => $this->getVisitorStatistics($businessId),
            'reviews' => $this->getReviewStatistics($businessId),
            'inquiries' => $this->getInquiryStatistics($businessId),
            'products' => $this->getProductStatistics($businessId),
            'adverts' => $this->getAdvertStatistics($businessId)
        ];
    }
    
    /**
     * Get visitor statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Visitor statistics
     */
    public function getVisitorStatistics(int $businessId): array
    {
        $query = "SELECT 
                     COUNT(*) as total_views,
                     COUNT(DISTINCT ip_address) as unique_visitors,
                     COUNT(CASE WHEN viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as views_last_7_days,
                     COUNT(CASE WHEN viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as views_last_30_days
                 FROM analytics_page_views
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Daily views for chart
        $dailyViewsQuery = "SELECT 
                              DATE(viewed_at) as date,
                              COUNT(*) as views
                           FROM analytics_page_views
                           WHERE business_id = :business_id
                           AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                           GROUP BY DATE(viewed_at)
                           ORDER BY date";
        
        $dailyStmt = $this->db->prepare($dailyViewsQuery);
        $dailyStmt->bindParam(':business_id', $businessId);
        $dailyStmt->execute();
        
        $dailyViews = [];
        while ($row = $dailyStmt->fetch(PDO::FETCH_ASSOC)) {
            $dailyViews[] = [
                'date' => $row['date'],
                'views' => (int) $row['views']
            ];
        }
        
        return [
            'total_views' => (int) $stats['total_views'],
            'unique_visitors' => (int) $stats['unique_visitors'],
            'views_last_7_days' => (int) $stats['views_last_7_days'],
            'views_last_30_days' => (int) $stats['views_last_30_days'],
            'daily_views' => $dailyViews
        ];
    }
    
    /**
     * Get review statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Review statistics
     */
    public function getReviewStatistics(int $businessId): array
    {
        $query = "SELECT 
                     COUNT(*) as total_reviews,
                     AVG(rating) as average_rating,
                     COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reviews,
                     COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reviews,
                     COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_reviews
                 FROM reviews
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Rating breakdown
        $ratingQuery = "SELECT 
                           rating,
                           COUNT(*) as count
                        FROM reviews
                        WHERE business_id = :business_id
                        AND status = 'approved'
                        GROUP BY rating
                        ORDER BY rating";
        
        $ratingStmt = $this->db->prepare($ratingQuery);
        $ratingStmt->bindParam(':business_id', $businessId);
        $ratingStmt->execute();
        
        $ratingBreakdown = [];
        while ($row = $ratingStmt->fetch(PDO::FETCH_ASSOC)) {
            $ratingBreakdown[] = [
                'rating' => (int) $row['rating'],
                'count' => (int) $row['count']
            ];
        }
        
        return [
            'total_reviews' => (int) $stats['total_reviews'],
            'average_rating' => round((float) $stats['average_rating'], 1),
            'approved_reviews' => (int) $stats['approved_reviews'],
            'pending_reviews' => (int) $stats['pending_reviews'],
            'new_reviews' => (int) $stats['new_reviews'],
            'rating_breakdown' => $ratingBreakdown
        ];
    }
    
    /**
     * Get inquiry statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Inquiry statistics
     */
    public function getInquiryStatistics(int $businessId): array
    {
        $query = "SELECT 
                     COUNT(*) as total_inquiries,
                     COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as inquiries_last_7_days,
                     COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as inquiries_last_30_days
                 FROM analytics_inquiries
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Weekly inquiries for chart
        $weeklyQuery = "SELECT 
                           YEARWEEK(created_at) as week,
                           COUNT(*) as count
                        FROM analytics_inquiries
                        WHERE business_id = :business_id
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
                        GROUP BY YEARWEEK(created_at)
                        ORDER BY week";
        
        $weeklyStmt = $this->db->prepare($weeklyQuery);
        $weeklyStmt->bindParam(':business_id', $businessId);
        $weeklyStmt->execute();
        
        $weeklyInquiries = [];
        while ($row = $weeklyStmt->fetch(PDO::FETCH_ASSOC)) {
            $weeklyInquiries[] = [
                'week' => $row['week'],
                'count' => (int) $row['count']
            ];
        }
        
        return [
            'total_inquiries' => (int) $stats['total_inquiries'],
            'inquiries_last_7_days' => (int) $stats['inquiries_last_7_days'],
            'inquiries_last_30_days' => (int) $stats['inquiries_last_30_days'],
            'weekly_inquiries' => $weeklyInquiries
        ];
    }
    
    /**
     * Get product statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Product statistics
     */
    public function getProductStatistics(int $businessId): array
    {
        $query = "SELECT 
                     COUNT(*) as total_products,
                     COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products,
                     COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_products
                 FROM products
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Product view counts
        $viewsQuery = "SELECT 
                          product_id,
                          COUNT(*) as views
                       FROM analytics_product_views
                       WHERE business_id = :business_id
                       GROUP BY product_id
                       ORDER BY views DESC
                       LIMIT 5";
        
        $viewsStmt = $this->db->prepare($viewsQuery);
        $viewsStmt->bindParam(':business_id', $businessId);
        $viewsStmt->execute();
        
        $topProducts = [];
        while ($row = $viewsStmt->fetch(PDO::FETCH_ASSOC)) {
            // Get product name
            $productQuery = "SELECT name FROM products WHERE id = :id";
            $productStmt = $this->db->prepare($productQuery);
            $productStmt->bindParam(':id', $row['product_id']);
            $productStmt->execute();
            $productName = $productStmt->fetchColumn();
            
            $topProducts[] = [
                'product_id' => (int) $row['product_id'],
                'name' => $productName,
                'views' => (int) $row['views']
            ];
        }
        
        return [
            'total_products' => (int) $stats['total_products'],
            'active_products' => (int) $stats['active_products'],
            'new_products' => (int) $stats['new_products'],
            'top_products' => $topProducts
        ];
    }
    
    /**
     * Get advert statistics for a business
     * 
     * @param int $businessId Business ID
     * @return array Advert statistics
     */
    public function getAdvertStatistics(int $businessId): array
    {
        $query = "SELECT 
                     COUNT(*) as total_adverts,
                     COUNT(CASE WHEN status = 'active' THEN 1 END) as active_adverts,
                     COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_adverts,
                     COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_adverts
                 FROM adverts
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Advert click stats
        $clicksQuery = "SELECT 
                           advert_id,
                           COUNT(*) as clicks
                        FROM analytics_advert_clicks
                        WHERE business_id = :business_id
                        GROUP BY advert_id
                        ORDER BY clicks DESC";
        
        $clicksStmt = $this->db->prepare($clicksQuery);
        $clicksStmt->bindParam(':business_id', $businessId);
        $clicksStmt->execute();
        
        $advertClicks = [];
        while ($row = $clicksStmt->fetch(PDO::FETCH_ASSOC)) {
            // Get advert title
            $advertQuery = "SELECT title FROM adverts WHERE id = :id";
            $advertStmt = $this->db->prepare($advertQuery);
            $advertStmt->bindParam(':id', $row['advert_id']);
            $advertStmt->execute();
            $advertTitle = $advertStmt->fetchColumn();
            
            $advertClicks[] = [
                'advert_id' => (int) $row['advert_id'],
                'title' => $advertTitle,
                'clicks' => (int) $row['clicks']
            ];
        }
        
        // Get available advert slots
        $businessQuery = "SELECT adverts_remaining FROM businesses WHERE id = :id";
        $businessStmt = $this->db->prepare($businessQuery);
        $businessStmt->bindParam(':id', $businessId);
        $businessStmt->execute();
        $advertsRemaining = (int) $businessStmt->fetchColumn();
        
        return [
            'total_adverts' => (int) $stats['total_adverts'],
            'active_adverts' => (int) $stats['active_adverts'],
            'pending_adverts' => (int) $stats['pending_adverts'],
            'expired_adverts' => (int) $stats['expired_adverts'],
            'adverts_remaining' => $advertsRemaining,
            'advert_clicks' => $advertClicks
        ];
    }
    
    /**
     * Log a page view for a business
     * 
     * @param int $businessId Business ID
     * @param string|null $ipAddress Visitor IP address
     * @param string|null $userAgent Visitor user agent
     * @param string|null $referrer Referrer URL
     * @return bool Success status
     */
    public function logPageView(
        int $businessId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $referrer = null
    ): bool {
        $query = "INSERT INTO analytics_page_views 
                 (business_id, ip_address, user_agent, referrer)
                 VALUES (:business_id, :ip_address, :user_agent, :referrer)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':business_id', $businessId);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->bindParam(':referrer', $referrer);
        
        return $stmt->execute();
    }
    
    /**
     * Log a product view
     * 
     * @param int $businessId Business ID
     * @param int $productId Product ID
     * @param string|null $ipAddress Visitor IP address
     * @return bool Success status
     */
    public function logProductView(int $businessId, int $productId, ?string $ipAddress = null): bool
    {
        $query = "INSERT INTO analytics_product_views 
                 (business_id, product_id, ip_address)
                 VALUES (:business_id, :product_id, :ip_address)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':business_id', $businessId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':ip_address', $ipAddress);
        
        return $stmt->execute();
    }
    
    /**
     * Log an advert click
     * 
     * @param int $businessId Business ID
     * @param int $advertId Advert ID
     * @param string|null $ipAddress Visitor IP address
     * @return bool Success status
     */
    public function logAdvertClick(int $businessId, int $advertId, ?string $ipAddress = null): bool
    {
        $query = "INSERT INTO analytics_advert_clicks 
                 (business_id, advert_id, ip_address)
                 VALUES (:business_id, :advert_id, :ip_address)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':business_id', $businessId);
        $stmt->bindParam(':advert_id', $advertId);
        $stmt->bindParam(':ip_address', $ipAddress);
        
        return $stmt->execute();
    }
    
    /**
     * Log a business inquiry
     * 
     * @param int $businessId Business ID
     * @param string $inquiryType Type of inquiry
     * @param string|null $ipAddress Visitor IP address
     * @return bool Success status
     */
    public function logInquiry(int $businessId, string $inquiryType, ?string $ipAddress = null): bool
    {
        $query = "INSERT INTO analytics_inquiries 
                 (business_id, inquiry_type, ip_address)
                 VALUES (:business_id, :inquiry_type, :ip_address)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':business_id', $businessId);
        $stmt->bindParam(':inquiry_type', $inquiryType);
        $stmt->bindParam(':ip_address', $ipAddress);
        
        return $stmt->execute();
    }
}
