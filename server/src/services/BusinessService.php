<?php

namespace App\Services;

use PDO;
use App\Models\Business;
use App\Models\Review;
use App\Models\Product;
use App\Models\Advert;
use App\Models\ImageService;
use InvalidArgumentException;

class BusinessService {
    private $db;
    private $business;
    private $review;
    private $product;
    private $advert;
    private $imageService;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->business = new Business($db);
        $this->review = new Review($db);
        $this->product = new Product($db);
        $this->advert = new Advert($db);
        $this->imageService = new ImageService();
    }
    
    /**
     * Create a new business
     * 
     * @param int $userId Owner user ID
     * @param array $data Business data
     * @param array $files Uploaded files (logo, cover_image)
     * @return array Created business details
     */
    public function createBusiness(int $userId, array $data, array $files = []): array {
        // Process logo upload if provided
        if (!empty($files['logo'])) {
            $logoPath = $this->imageService->uploadImage($files['logo'], 'businesses', 'logo_');
            if ($logoPath) {
                $data['logo'] = $logoPath;
            }
        }
        
        // Process cover image upload if provided
        if (!empty($files['cover_image'])) {
            $coverPath = $this->imageService->uploadImage($files['cover_image'], 'businesses', 'cover_');
            if ($coverPath) {
                $data['cover_image'] = $coverPath;
            }
        }
        
        // Handle social media as JSON
        if (!empty($data['social_media']) && is_array($data['social_media'])) {
            $data['social_media'] = json_encode($data['social_media']);
        }
        
        // Handle business hours as JSON
        if (!empty($data['business_hours']) && is_array($data['business_hours'])) {
            $data['business_hours'] = json_encode($data['business_hours']);
        }
        
        // Set defaults
        $data['user_id'] = $userId;
        $data['package_type'] = $data['package_type'] ?? 'Basic';
        $data['verification_status'] = 'pending';
        $data['adverts_remaining'] = 0;
        
        // Create business
        $businessId = $this->business->createBusiness($data);
        
        // Return created business
        return $this->getBusinessDetails($businessId);
    }
    
    /**
     * Update a business
     * 
     * @param int $businessId Business ID
     * @param array $data Updated data
     * @param array $files Uploaded files (logo, cover_image)
     * @return array Updated business details
     */
    public function updateBusiness(int $businessId, array $data, array $files = []): array {
        // Get existing business
        $existingBusiness = $this->business->getBusinessById($businessId);
        if (!$existingBusiness) {
            throw new \Exception('Business not found');
        }
        
        // Process logo upload if provided
        if (!empty($files['logo'])) {
            $logoPath = $this->imageService->uploadImage($files['logo'], 'businesses', 'logo_');
            if ($logoPath) {
                // Delete old logo if exists
                if (!empty($existingBusiness['logo'])) {
                    $this->imageService->deleteImage($existingBusiness['logo']);
                }
                $data['logo'] = $logoPath;
            }
        }
        
        // Process cover image upload if provided
        if (!empty($files['cover_image'])) {
            $coverPath = $this->imageService->uploadImage($files['cover_image'], 'businesses', 'cover_');
            if ($coverPath) {
                // Delete old cover image if exists
                if (!empty($existingBusiness['cover_image'])) {
                    $this->imageService->deleteImage($existingBusiness['cover_image']);
                }
                $data['cover_image'] = $coverPath;
            }
        }
        
        // Handle social media as JSON
        if (!empty($data['social_media']) && is_array($data['social_media'])) {
            $data['social_media'] = json_encode($data['social_media']);
        }
        
        // Handle business hours as JSON
        if (!empty($data['business_hours']) && is_array($data['business_hours'])) {
            $data['business_hours'] = json_encode($data['business_hours']);
        }
        
        // Update business
        $this->business->updateBusiness($businessId, $data);
        
        // Return updated business
        return $this->getBusinessDetails($businessId);
    }
    
    /**
     * Get complete business details with related data
     * 
     * @param int $businessId Business ID
     * @return array Business details with related data
     */
    public function getBusinessDetails(int $businessId): array {
        // Get business
        $business = $this->business->getBusinessById($businessId);
        if (!$business) {
            throw new \Exception('Business not found');
        }
        
        // Get products if premium package
        $products = [];
        if (in_array($business['package_type'], ['Silver', 'Gold'])) {
            $products = $this->product->getProductsByBusinessId($businessId);
        }
        
        // Get approved reviews
        $reviews = $this->review->getApprovedReviewsByBusinessId($businessId);
        
        // Get active adverts
        $adverts = $this->advert->getActiveAdvertsByBusinessId($businessId);
        
        // Parse JSON fields
        if (!empty($business['social_media']) && is_string($business['social_media'])) {
            $business['social_media'] = json_decode($business['social_media'], true);
        }
        
        if (!empty($business['business_hours']) && is_string($business['business_hours'])) {
            $business['business_hours'] = json_decode($business['business_hours'], true);
        }
        
        // Calculate average rating
        $avgRating = 0;
        $reviewCount = count($reviews);
        if ($reviewCount > 0) {
            $totalRating = array_sum(array_column($reviews, 'rating'));
            $avgRating = round($totalRating / $reviewCount, 1);
        }
        
        // Return complete business details
        return [
            'business' => $business,
            'products' => $products,
            'reviews' => $reviews,
            'adverts' => $adverts,
            'rating' => [
                'average' => $avgRating,
                'count' => $reviewCount
            ]
        ];
    }
    
    /**
     * Delete a business
     * 
     * @param int $businessId Business ID
     * @return bool Success status
     */
    public function deleteBusiness(int $businessId): bool {
        // Get business to delete its files
        $business = $this->business->getBusinessById($businessId);
        if (!$business) {
            return false;
        }
        
        // Delete logo if exists
        if (!empty($business['logo'])) {
            $this->imageService->deleteImage($business['logo']);
        }
        
        // Delete cover image if exists
        if (!empty($business['cover_image'])) {
            $this->imageService->deleteImage($business['cover_image']);
        }
        
        // Delete all related products and their images
        $products = $this->product->getProductsByBusinessId($businessId);
        foreach ($products as $product) {
            if (!empty($product['image'])) {
                $this->imageService->deleteImage($product['image']);
            }
        }
        
        // Delete all related adverts and their images
        $adverts = $this->advert->getAdvertsByBusinessId($businessId);
        foreach ($adverts as $advert) {
            if (!empty($advert['image'])) {
                $this->imageService->deleteImage($advert['image']);
            }
        }
        
        // Delete business and all related data will cascade
        return $this->business->deleteBusiness($businessId);
    }
    
    /**
     * Verify a business
     * 
     * @param int $businessId Business ID
     * @param string $status Verification status (verified, rejected)
     * @return bool Success status
     */
    public function verifyBusiness(int $businessId, string $status): bool {
        if (!in_array($status, ['verified', 'rejected'])) {
            throw new \InvalidArgumentException('Invalid verification status');
        }
        
        return $this->business->updateBusiness($businessId, ['verification_status' => $status]);
    }
    
    /**
     * Get businesses owned by a user
     * 
     * @param int $userId User ID
     * @return array User's businesses
     */
    public function getUserBusinesses(int $userId): array {
        return $this->business->getBusinessesByUserId($userId);
    }
    
    /**
     * Check if a business has access to a specific feature based on their package tier
     * 
     * @param int $businessId Business ID
     * @param string $feature Feature to check
     * @return bool Whether the business has access to the feature
     * @throws InvalidArgumentException If the feature is invalid
     */
    public function checkFeatureAccess(int $businessId, string $feature): bool {
        $business = $this->business->getBusinessById($businessId);
        
        if (!$business) {
            throw new Exception('Business not found');
        }
        
        $featureMatrix = [
            'website' => ['Bronze', 'Silver', 'Gold'],
            'whatsapp' => ['Bronze', 'Silver', 'Gold'],
            'products' => ['Silver', 'Gold'],
            'adverts' => ['Silver', 'Gold'],
            'social_boost' => ['Gold']
        ];

        if (!isset($featureMatrix[$feature])) {
            throw new InvalidArgumentException("Invalid feature: $feature");
        }

        return in_array($business['package_type'], $featureMatrix[$feature]);
    }

    /**
     * Get tier badge data for a package tier
     * 
     * @param string $tier Package tier
     * @return array Badge data with color and label
     */
    public function getTierBadgeData(string $tier): array {
        return match($tier) {
            'Gold' => ['color' => 'bg-amber-400', 'label' => 'Gold Member'],
            'Silver' => ['color' => 'bg-gray-300', 'label' => 'Silver Member'],
            'Bronze' => ['color' => 'bg-orange-600', 'label' => 'Bronze Member'],
            default => ['color' => 'bg-gray-100', 'label' => 'Basic Listing']
        };
    }
    
    /**
     * Check if a business is at or exceeding their tier limit for a feature
     *
     * @param int $businessId Business ID
     * @param string $feature Feature to check (products, adverts)
     * @return bool Whether the business is at their tier limit
     */
    public function isAtTierLimit(int $businessId, string $feature): bool {
        $business = $this->business->getBusinessById($businessId);
        
        if (!$business) {
            throw new Exception('Business not found');
        }
        
        // Define limits for each tier
        $limits = [
            'products' => [
                'Basic' => 0,
                'Bronze' => 0,
                'Silver' => 10,
                'Gold' => 30
            ],
            'adverts' => [
                'Basic' => 0,
                'Bronze' => 0,
                'Silver' => 3,
                'Gold' => 10
            ]
        ];
        
        if (!isset($limits[$feature])) {
            throw new InvalidArgumentException("Invalid feature: $feature");
        }
        
        $tier = $business['package_type'];
        $limit = $limits[$feature][$tier] ?? 0;
        
        // Count current usage
        $count = 0;
        switch ($feature) {
            case 'products':
                $count = count($this->product->getProductsByBusinessId($businessId));
                break;
            case 'adverts':
                $count = count($this->advert->getAllAdvertsByBusinessId($businessId));
                break;
        }
        
        return $count >= $limit;
    }
    
    /**
     * Check if a business has access to a specific feature based on their package
     * 
     * @param int $businessId Business ID
     * @param string $feature Feature name to check access for
     * @return bool|int Whether the business has access (or count for limited features)
     * @throws NotFoundException If business not found
     */
    public function checkFeatureAccessOld(int $businessId, string $feature) {
        // Get the business tier
        $stmt = $this->db->prepare("SELECT package_type FROM businesses WHERE id = ?");
        $stmt->execute([$businessId]);
        $business = $stmt->fetch();
        
        if (!$business) {
            throw new \Exception("Business not found");
        }
        
        $tier = $business['package_type'];
        
        // Define feature access by tier
        $features = [
            // Boolean features - true means access is allowed
            'website' => [
                'Basic' => false,
                'Silver' => true, 
                'Gold' => true
            ],
            'social_media' => [
                'Basic' => true,
                'Silver' => true,
                'Gold' => true
            ],
            'analytics' => [
                'Basic' => false,
                'Silver' => true,
                'Gold' => true
            ],
            'featured_listing' => [
                'Basic' => false,
                'Silver' => false,
                'Gold' => true
            ],
            
            // Count features - number indicates the limit
            'products' => [
                'Basic' => 5,
                'Silver' => 15,
                'Gold' => 30
            ],
            'adverts' => [
                'Basic' => 0,
                'Silver' => 1,
                'Gold' => 3
            ],
            'images' => [
                'Basic' => 3,
                'Silver' => 10,
                'Gold' => 20
            ]
        ];
        
        // If feature doesn't exist in our definitions, deny access
        if (!isset($features[$feature])) {
            return false;
        }
        
        // Return the appropriate value (boolean or count)
        return $features[$feature][$tier];
    }
    
    /**
     * Get counts for various business items to check against limits
     * 
     * @param int $businessId The business ID
     * @param string $itemType Type of item to count (products, adverts, images)
     * @return int Current count
     */
    public function getItemCount(int $businessId, string $itemType): int {
        switch ($itemType) {
            case 'products':
                $sql = "SELECT COUNT(*) as count FROM products WHERE business_id = ?";
                break;
            case 'adverts':
                $sql = "SELECT COUNT(*) as count FROM adverts WHERE business_id = ?";
                break;
            case 'images':
                $sql = "SELECT COUNT(*) as count FROM business_images WHERE business_id = ?";
                break;
            default:
                return 0;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$businessId]);
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
}
