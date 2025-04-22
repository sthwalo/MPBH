<?php

namespace App\Services;

use PDO;
use App\Models\User;
use App\Exceptions\BadRequestException;

/**
 * Service responsible for business registration process
 */
class BusinessRegistrationService
{
    /**
     * @param BusinessService $businessService Service for business entity management
     * @param ImageService $imageService Service for image processing
     * @param AnalyticsService $analyticsService Service for tracking analytics
     */
    public function __construct(
        private BusinessService $businessService,
        private ImageService $imageService,
        private AnalyticsService $analyticsService
    ) {}
    
    /**
     * Register a new business with optional image uploads
     *
     * @param object $user Authenticated user object
     * @param array $data Business data
     * @param array $files Uploaded files (logo, cover_image)
     * @return array Created business details
     * @throws BadRequestException If required fields are missing
     */
    public function register(object $user, array $data, array $files = []): array
    {
        // Validate required fields
        $requiredFields = ['name', 'category', 'district', 'contact_email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new BadRequestException("Missing required field: {$field}");
            }
        }
        
        // Process image uploads if provided
        $processedFiles = [];
        
        if (!empty($files['logo'])) {
            $processedFiles['logo'] = $files['logo'];
        }
        
        if (!empty($files['cover_image'])) {
            $processedFiles['cover_image'] = $files['cover_image'];
        }
        
        // Create the business in the database
        $business = $this->businessService->createBusiness($user->id, $data, $processedFiles);
        
        // Track the registration event
        $this->analyticsService->logInteraction($business['id'], 'business_registration', [
            'package_type' => $business['package_type']
        ]);
        
        return $business;
    }
    
    /**
     * Upload a business image (logo or cover image)
     *
     * @param object $user Authenticated user object
     * @param object $uploadedFile The uploaded file
     * @param string $type Image type (logo or cover_image)
     * @return string The path to the uploaded image
     */
    public function uploadBusinessImage(object $user, object $uploadedFile, string $type): string
    {
        // Use consolidated ImageService to upload image
        $imagePath = $this->imageService->uploadFromPsr7(
            $uploadedFile,
            'businesses/' . $user->business_id . '/' . $type
        );
        
        // Update the business record with the new image path
        $updateData = [$type => $imagePath];
        $this->businessService->updateBusiness($user->business_id, $updateData);
        
        return $imagePath;
    }
}