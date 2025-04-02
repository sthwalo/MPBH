<?php

namespace App\Services;

use PDO;
use App\Models\Business;
use App\Models\Review;
use App\Models\Product;
use App\Models\Advert;

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
}
