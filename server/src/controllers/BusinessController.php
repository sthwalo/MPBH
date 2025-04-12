<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\ResponseHelper;
use App\Models\Business;
use App\Models\Product;
use App\Models\Review;
use Monolog\Logger;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @OA\Tag(
 *     name="Business",
 *     description="Business management endpoints"
 * )
 */
class BusinessController
{
    private PDO $db;
    private Logger $logger;
    
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * Get all businesses with filters, sorting, and pagination
     * 
     * @OA\Get(
     *     path="/businesses",
     *     tags={"Business"},
     *     summary="Get list of businesses",
     *     description="Retrieve a paginated list of businesses with optional filters",
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="district",
     *         in="query",
     *         description="Filter by district",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc/desc)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of businesses",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Business")
     *         )
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function getAllBusinesses(Request $request, Response $response): Response
    {
        $this->logger->info('Fetching businesses', [
            'query' => $request->getQueryParams()
        ]);
        
        // Get query parameters
        $params = $request->getQueryParams();
        
        // Extract filters
        $filters = [
            'category' => $params['category'] ?? null,
            'district' => $params['district'] ?? null,
            'search' => $params['search'] ?? null,
            'verification_status' => 'verified' // Only show verified businesses
        ];
        
        // Clean up filters (remove null values)
        $filters = array_filter($filters);
        
        // Set pagination params
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $limit = isset($params['limit']) ? (int) $params['limit'] : 20;
        
        // Validate pagination params
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        // Set sorting params
        $sortBy = $params['sort'] ?? 'name';
        $order = $params['order'] ?? 'asc';
        
        // Get businesses
        $business = new Business($this->db);
        $result = $business->readAll($filters, $page, $limit, $sortBy, $order);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $result
        ];
        
        // Add cache headers for improved performance
        $headers = [
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=600',
            'Vary' => 'Accept, Accept-Encoding'
        ];
        
        return ResponseHelper::success($response, $responseData, 200, $headers);
    }
    
    /**
     * Get a specific business by ID
     * 
     * @OA\Get(
     *     path="/businesses/{id}",
     *     tags={"Business"},
     *     summary="Get a business by ID",
     *     description="Retrieve a business by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Business ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business details",
     *         @OA\JsonContent(ref="#/components/schemas/Business")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Business not found"
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function getBusinessById(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($id)) {
            throw new NotFoundException('Business not found');
        }
        
        // Track page view (in a real app, we would add analytics)
        $this->logPageView($id);
        
        // Prepare response
        $businessData = $business->toArray();
        
        // Get additional data (reviews, products) if needed
        $includeReviews = isset($request->getQueryParams()['include_reviews']) && $request->getQueryParams()['include_reviews'] === 'true';
        $includeProducts = isset($request->getQueryParams()['include_products']) && $request->getQueryParams()['include_products'] === 'true';
        
        if ($includeReviews) {
            $review = new Review($this->db);
            $businessData['reviews'] = $review->getBusinessReviews($id);
        }
        
        if ($includeProducts && in_array($business->package_type, ['Silver', 'Gold'])) {
            $product = new Product($this->db);
            $businessData['products'] = $product->getBusinessProducts($id);
        }
        
        $responseData = [
            'status' => 'success',
            'data' => $businessData
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Get products for a specific business
     * 
     * @OA\Get(
     *     path="/businesses/{id}/products",
     *     tags={"Business"},
     *     summary="Get products for a business",
     *     description="Retrieve a list of products for a business",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Business ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Business not found"
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function getBusinessProducts(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($id)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if business has products feature
        if (!in_array($business->package_type, ['Silver', 'Gold'])) {
            // Return empty products array rather than an error
            $responseData = [
                'status' => 'success',
                'data' => []
            ];
            
            return ResponseHelper::success($response, $responseData, 200);
        }
        
        // Get products
        $product = new Product($this->db);
        $products = $product->getBusinessProducts($id);
        
        $responseData = [
            'status' => 'success',
            'data' => $products
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Get reviews for a specific business
     * 
     * @OA\Get(
     *     path="/businesses/{id}/reviews",
     *     tags={"Business"},
     *     summary="Get reviews for a business",
     *     description="Retrieve a list of reviews for a business",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Business ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Business not found"
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function getBusinessReviews(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($id)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get reviews
        $review = new Review($this->db);
        $reviews = $review->getBusinessReviews($id);
        
        $responseData = [
            'status' => 'success',
            'data' => $reviews
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Get authenticated user's business
     * 
     * @OA\Get(
     *     path="/my-business",
     *     tags={"Business"},
     *     summary="Get authenticated user's business",
     *     description="Retrieve the business associated with the authenticated user",
     *     @OA\Response(
     *         response=200,
     *         description="Business details",
     *         @OA\JsonContent(ref="#/components/schemas/Business")
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function getMyBusiness(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get statistics
        $stats = $business->getStatistics();
        
        // Get subscription info (if any)
        $subscriptionInfo = null;
        if ($business->subscription_id) {
            // In a real app, this would retrieve subscription details
            // For now, we'll use a placeholder
            $subscriptionInfo = [
                'status' => 'active',
                'amount' => $business->package_type === 'Gold' ? 1000 : ($business->package_type === 'Silver' ? 500 : 200),
                'next_billing_date' => date('Y-m-d', strtotime('+1 month'))
            ];
        }
        
        // Prepare response
        $businessData = $business->toArray(true); // Include private fields
        $businessData['statistics'] = $stats;
        $businessData['subscription'] = $subscriptionInfo;
        
        $responseData = [
            'status' => 'success',
            'data' => $businessData
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Update authenticated user's business details
     * 
     * @OA\Patch(
     *     path="/my-business",
     *     tags={"Business"},
     *     summary="Update authenticated user's business details",
     *     description="Update the business associated with the authenticated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BusinessUpdate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business updated",
     *         @OA\JsonContent(ref="#/components/schemas/Business")
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function updateMyBusiness(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Update business fields
        $updatableFields = [
            'name',
            'category',
            'district',
            'address',
            'phone',
            'website',
            'description',
            'social_media',
            'business_hours',
            'longitude',
            'latitude'
        ];
        
        foreach ($updatableFields as $field) {
            if (isset($data[$field])) {
                $business->$field = $data[$field];
            }
        }
        
        // Update business
        if (!$business->update()) {
            throw new \Exception('Failed to update business');
        }
        
        $this->logger->info('Business updated', ['business_id' => $businessId]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Business updated successfully',
            'data' => $business->toArray()
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Upload business logo
     * 
     * @OA\Post(
     *     path="/my-business/logo",
     *     tags={"Business"},
     *     summary="Upload business logo",
     *     description="Upload a logo for the business",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logo uploaded",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="logo",
     *                 type="string",
     *                 format="uri"
     *             )
     *         )
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function uploadLogo(Request $request, Response $response): Response
    {
        return $this->handleImageUpload($request, $response, 'logo');
    }
    
    /**
     * Upload business cover image
     * 
     * @OA\Post(
     *     path="/my-business/cover",
     *     tags={"Business"},
     *     summary="Upload business cover image",
     *     description="Upload a cover image for the business",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cover image uploaded",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="cover_image",
     *                 type="string",
     *                 format="uri"
     *             )
     *         )
     *     ),
     *     security={"bearerAuth": {}}
     * )
     */
    public function uploadCover(Request $request, Response $response): Response
    {
        return $this->handleImageUpload($request, $response, 'cover_image');
    }
    
    /**
     * Handle image upload for logo or cover
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param string $type Image type ('logo' or 'cover_image')
     * @return Response JSON response
     */
    private function handleImageUpload(Request $request, Response $response, string $type): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get uploaded file
        $uploadedFiles = $request->getUploadedFiles();
        
        if (empty($uploadedFiles['image'])) {
            throw new BadRequestException('No image file uploaded');
        }
        
        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $uploadedFiles['image'];
        
        // Validate file
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestException('Upload failed with error code ' . $uploadedFile->getError());
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile->getClientMediaType(), $allowedTypes)) {
            throw new ValidationException('Validation failed', [
                'image' => 'File must be an image (JPEG, PNG, or GIF)'
            ]);
        }
        
        // Generate unique filename
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s_%s.%s', $type, $basename, $extension);
        
        // Create uploads directory if it doesn't exist
        $directory = __DIR__ . '/../../public/uploads/businesses/' . $businessId;
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Move the uploaded file to the uploads directory
        $uploadedFile->moveTo($directory . '/' . $filename);
        
        // Update business with new image path
        $imagePath = '/uploads/businesses/' . $businessId . '/' . $filename;
        $business->updateImage($type, $imagePath);
        
        $this->logger->info('Business image uploaded', ['business_id' => $business->id, 'type' => $type]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => ucfirst($type) . ' uploaded successfully',
            'data' => [
                $type => $imagePath
            ]
        ];
        
        return ResponseHelper::success($response, $responseData, 200);
    }
    
    /**
     * Log a page view for analytics
     * 
     * @param int $businessId Business ID
     * @return void
     */
    private function logPageView(int $businessId): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO analytics_page_views (business_id, ip_address, user_agent, referrer) 
                 VALUES (:business_id, :ip_address, :user_agent, :referrer)"
            );
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $referrer = $_SERVER['HTTP_REFERER'] ?? null;
            
            $stmt->bindParam(':business_id', $businessId);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':referrer', $referrer);
            
            $stmt->execute();
        } catch (\Exception $e) {
            // Log the error but don't expose it to the user
            $this->logger->error('Failed to log page view', [
                'business_id' => $businessId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
