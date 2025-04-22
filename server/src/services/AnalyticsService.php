<?php

namespace App\Services;

use PDO;
use App\Models\Statistic;
use App\Models\Business;
use App\Models\PageView;
use App\Repositories\PageViewRepository;
use App\Exceptions\AnalyticsException;

class AnalyticsService {
    private $db;
    private $statistic;
    private $business;
    private ?PageViewRepository $pageViewRepository = null;
    
    public function __construct(PDO $db, ?PageViewRepository $pageViewRepository = null) {
        $this->db = $db;
        $this->statistic = new Statistic($db);
        $this->business = new Business($db);
        $this->pageViewRepository = $pageViewRepository;
    }
    
    /**
     * Get dashboard statistics for a business
     * 
     * @param int $businessId
     * @return array Dashboard statistics
     */
    public function getDashboardStatistics(int $businessId): array {
        // Get business details
        $business = $this->business->getBusinessById($businessId);
        if (!$business) {
            throw new \Exception('Business not found');
        }
        
        // Get visitors statistics
        $pageViews = $this->statistic->getPageViews($businessId);
        $uniqueVisitors = $this->statistic->getUniqueVisitors($businessId);
        $recentViews = $this->statistic->getRecentPageViews($businessId, 30);
        
        // Get reviews statistics
        $reviews = $this->statistic->getReviewsStats($businessId);
        
        // Get inquiries statistics
        $inquiries = $this->statistic->getInquiriesStats($businessId);
        
        // Get product statistics
        $products = $this->statistic->getProductStats($businessId);
        
        // Get advert statistics
        $adverts = $this->statistic->getAdvertStats($businessId);
        
        // Get payment statistics
        $payments = $this->statistic->getPaymentStats($businessId);
        
        // Daily views for the past 30 days
        $dailyViews = $this->statistic->getDailyViewsData($businessId, 30);
        
        // Weekly inquiries for the past 12 weeks
        $weeklyInquiries = $this->statistic->getWeeklyInquiriesData($businessId, 12);
        
        return [
            'business' => [
                'id' => $business['id'],
                'name' => $business['name'],
                'package_type' => $business['package_type'],
                'verification_status' => $business['verification_status'],
                'adverts_remaining' => $business['adverts_remaining']
            ],
            'statistics' => [
                'visitors' => [
                    'total_views' => $pageViews['total'] ?? 0,
                    'unique_visitors' => $uniqueVisitors['total'] ?? 0,
                    'views_last_7_days' => $this->statistic->getRecentPageViews($businessId, 7)['total'] ?? 0,
                    'views_last_30_days' => $recentViews['total'] ?? 0,
                    'daily_views' => $dailyViews
                ],
                'reviews' => [
                    'total_reviews' => $reviews['total'] ?? 0,
                    'average_rating' => $reviews['average_rating'] ?? 0,
                    'approved_reviews' => $reviews['approved'] ?? 0,
                    'pending_reviews' => $reviews['pending'] ?? 0,
                    'new_reviews' => $reviews['new'] ?? 0,
                    'rating_breakdown' => [
                        ['rating' => 1, 'count' => $reviews['rating_1'] ?? 0],
                        ['rating' => 2, 'count' => $reviews['rating_2'] ?? 0],
                        ['rating' => 3, 'count' => $reviews['rating_3'] ?? 0],
                        ['rating' => 4, 'count' => $reviews['rating_4'] ?? 0],
                        ['rating' => 5, 'count' => $reviews['rating_5'] ?? 0]
                    ]
                ],
                'inquiries' => [
                    'total_inquiries' => $inquiries['total'] ?? 0,
                    'inquiries_last_7_days' => $inquiries['last_7_days'] ?? 0,
                    'inquiries_last_30_days' => $inquiries['last_30_days'] ?? 0,
                    'weekly_inquiries' => $weeklyInquiries
                ],
                'products' => [
                    'total_products' => $products['total'] ?? 0,
                    'active_products' => $products['active'] ?? 0,
                    'new_products' => $products['new'] ?? 0,
                    'top_products' => $this->statistic->getTopProducts($businessId, 5)
                ],
                'adverts' => [
                    'total_adverts' => $adverts['total'] ?? 0,
                    'active_adverts' => $adverts['active'] ?? 0,
                    'pending_adverts' => $adverts['pending'] ?? 0,
                    'expired_adverts' => $adverts['expired'] ?? 0,
                    'adverts_remaining' => $business['adverts_remaining'],
                    'advert_clicks' => $this->statistic->getTopAdverts($businessId, 5)
                ]
            ],
            'payments' => [
                'total_spent' => $payments['total_spent'] ?? 0,
                'successful_payments' => $payments['successful_payments'] ?? 0,
                'failed_payments' => $payments['failed_payments'] ?? 0,
                'last_payment_date' => $payments['last_payment_date'] ?? null
            ]
        ];
    }
    
    /**
     * Get location-based traffic statistics
     * 
     * @param int $businessId
     * @return array Location statistics
     */
    public function getLocationStatistics(int $businessId): array {
        return $this->statistic->getLocationStats($businessId);
    }
    
    /**
     * Get referral source statistics
     * 
     * @param int $businessId
     * @return array Referral statistics
     */
    public function getReferralStatistics(int $businessId): array {
        return $this->statistic->getReferralStats($businessId);
    }
    
    /**
     * Log user interaction with a business
     * 
     * @param int $businessId
     * @param string $type
     * @param array $data
     * @return bool Success status
     */
    public function logInteraction(int $businessId, string $type, array $data): bool {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        switch ($type) {
            case 'page_view':
                return $this->statistic->logPageView([
                    'business_id' => $businessId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'referrer' => $_SERVER['HTTP_REFERER'] ?? ''
                ]);
                
            case 'product_view':
                if (!isset($data['product_id'])) {
                    return false;
                }
                
                return $this->statistic->logProductView([
                    'business_id' => $businessId,
                    'product_id' => $data['product_id'],
                    'ip_address' => $ipAddress
                ]);
                
            case 'advert_click':
                if (!isset($data['advert_id'])) {
                    return false;
                }
                
                return $this->statistic->logAdvertClick([
                    'business_id' => $businessId,
                    'advert_id' => $data['advert_id'],
                    'ip_address' => $ipAddress
                ]);
                
            case 'inquiry':
                if (!isset($data['inquiry_type'])) {
                    return false;
                }
                
                return $this->statistic->logInquiry([
                    'business_id' => $businessId,
                    'inquiry_type' => $data['inquiry_type'],
                    'ip_address' => $ipAddress
                ]);
                
            default:
                return false;
        }
    }
    
    /**
     * Log a page view using repository pattern if available
     * 
     * @param int $businessId Business ID
     * @param string $ip IP address of visitor
     * @param string $userAgent User agent string
     * @param string|null $referrer Referrer URL
     * @throws AnalyticsException If logging fails
     */
    public function logPageView(int $businessId, string $ip, string $userAgent, ?string $referrer = null): void
    {
        // First try to use the repository pattern if available
        if ($this->pageViewRepository !== null) {
            try {
                $view = new PageView();
                $view->business_id = $businessId;
                $view->ip_address = $ip;
                $view->user_agent = $userAgent;
                $view->referrer = $referrer;
                
                $this->pageViewRepository->create($view);
            } catch (\Exception $e) {
                throw new AnalyticsException('Failed to log page view through repository', $e);
            }
        } else {
            // Fall back to the legacy statistic implementation
            $success = $this->statistic->logPageView([
                'business_id' => $businessId,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'referrer' => $referrer ?? ''
            ]);
            
            if (!$success) {
                throw new AnalyticsException('Failed to log page view');
            }
        }
    }
    
    /**
     * Get analytics stats using repository pattern if available
     * 
     * @param int $businessId Business ID
     * @return array Statistics data
     */
    public function getViewStats(int $businessId): array
    {
        if ($this->pageViewRepository !== null) {
            return $this->pageViewRepository->getStats($businessId);
        }
        
        // Fall back to legacy implementation
        return [
            'total_views' => $this->statistic->getPageViews($businessId)['total'] ?? 0,
            'unique_visitors' => $this->statistic->getUniqueVisitors($businessId)['total'] ?? 0,
            'recent_views' => $this->statistic->getRecentPageViews($businessId, 30)['total'] ?? 0,
        ];
    }
}
