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
use App\Services\BusinessService;
use App\Services\ImageService;

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
    private BusinessService $businessService;
    private ImageService $imageService;
    
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->businessService = new BusinessService($db);
        $this->imageService = new ImageService();
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
    try {
        $this->logger->info('Fetching businesses', [
            'query' => $request->getQueryParams()
        ]);
        
        $params = $request->getQueryParams();
        $filters = [
            'category' => $params['category'] ?? null,
            'district' => $params['district'] ?? null,
            'search' => $params['search'] ?? null,
            'verification_status' => 'verified'
        ];
        
        $filters = array_filter($filters);
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(max(1, (int)($params['limit'] ?? 20)), 100); // Fixed the syntax here
        $sortBy = $params['sort'] ?? 'name';
        $order = $params['order'] ?? 'asc';
        
        $business = new Business($this->db);
        $result = $business->readAll($filters, $page, $limit, $sortBy, $order);
        
        return ResponseHelper::success($response, [
            'status' => 'success',
            'data' => $result
        ], 200, [
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=600',
            'Vary' => 'Accept, Accept-Encoding'
        ]);
        
    } catch (\Exception $e) {
        $this->logger->error('Error fetching businesses: ' . $e->getMessage());
        throw $e;
    }
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
    public function getBusinessById(Request $request, Response $response, array $args): Response {
        try {
            $id = (int) $args['id'];
            $cacheKey = "business:{$id}";
            
            // Check cache first
            if ($cached = $this->cache->get($cacheKey)) {
                return ResponseHelper::success($response, [
                    'status' => 'success',
                    'data' => $cached,
                    'from_cache' => true
                ]);
            }
            
            $business = new Business($this->db);
            
            if (!$business->readOne($id)) {
                throw new NotFoundException('Business not found');
            }
            
            $this->logPageView($id);
            $businessData = $business->toArray();
            $queryParams = $request->getQueryParams();
            
            if (($queryParams['include_reviews'] ?? '') === 'true') {
                $review = new Review($this->db);
                $businessData['reviews'] = $review->getBusinessReviews($id);
            }
            
            if (($queryParams['include_products'] ?? '') === 'true' && in_array($business->package_type, ['Silver', 'Gold'])) {
                $product = new Product($this->db);
                $businessData['products'] = $product->getBusinessProducts($id);
            }
            
            // Cache the result
            $this->cache->set($cacheKey, $businessData, 3600);
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'data' => $businessData
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error fetching business {$id}: " . $e->getMessage());
            throw $e;
        }
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
        try {
            $id = (int) $args['id'];
            $business = new Business($this->db);
            
            if (!$business->readOne($id)) {
                throw new NotFoundException('Business not found');
            }
            
            if (!in_array($business->package_type, ['Silver', 'Gold'])) {
                return ResponseHelper::success($response, [
                    'status' => 'success',
                    'data' => []
                ]);
            }
            
            $product = new Product($this->db);
            $products = $product->getBusinessProducts($id);
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'data' => $products
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error fetching products for business {$id}: " . $e->getMessage());
            throw $e;
        }
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
        try {
            $id = (int) $args['id'];
            $business = new Business($this->db);
            
            if (!$business->readOne($id)) {
                throw new NotFoundException('Business not found');
            }
            
            $review = new Review($this->db);
            $reviews = $review->getBusinessReviews($id);
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'data' => $reviews
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error fetching reviews for business {$id}: " . $e->getMessage());
            throw $e;
        }
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
        try {
            $userData = $request->getAttribute('user');
            $businessId = $userData->business_id;
            $business = new Business($this->db);
            
            if (!$business->readOne($businessId)) {
                throw new NotFoundException('Business not found');
            }
            
            $stats = $business->getStatistics();
            $subscriptionInfo = null;
            
            if ($business->subscription_id) {
                $subscriptionInfo = [
                    'status' => 'active',
                    'amount' => $business->package_type === 'Gold' ? 1000 : ($business->package_type === 'Silver' ? 500 : 200),
                    'next_billing_date' => date('Y-m-d', strtotime('+1 month'))
                ];
            }
            
            $businessData = $business->toArray(true);
            $businessData['statistics'] = $stats;
            $businessData['subscription'] = $subscriptionInfo;
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'data' => $businessData
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error fetching user's business: " . $e->getMessage());
            throw $e;
        }
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
        try {
            $data = $request->getParsedBody();
            
            // Add validation
            $validator = new BusinessValidator();
            $errors = $validator->validate($data);
            if (!empty($errors)) {
                throw new ValidationException('Invalid input', $errors);
            }

            $userData = $request->getAttribute('user');
            $businessId = $userData->business_id;
            $business = new Business($this->db);
            
            if (!$business->readOne($businessId)) {
                throw new NotFoundException('Business not found');
            }
            
            $data = $request->getParsedBody();
            $updatableFields = [
                'name', 'category', 'district', 'address', 'phone', 
                'website', 'description', 'social_media', 'business_hours',
                'longitude', 'latitude'
            ];
            
            foreach ($updatableFields as $field) {
                if (isset($data[$field])) {
                    $business->$field = $data[$field];
                }
            }
            
            if (!$business->update()) {
                throw new \Exception('Failed to update business');
            }
            
            $this->logger->info('Business updated', ['business_id' => $businessId]);
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'message' => 'Business updated successfully',
                'data' => $business->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error updating business: " . $e->getMessage());
            throw $e;
        }
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
     */
    private function handleImageUpload(Request $request, Response $response, string $type): Response
    {
        try {
            $userData = $request->getAttribute('user');
            $businessId = $userData->business_id;
            $business = new Business($this->db);
            
            if (!$business->readOne($businessId)) {
                throw new NotFoundException('Business not found');
            }
            
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles['image'])) {
                throw new BadRequestException('No image file uploaded');
            }
            
            /** @var UploadedFileInterface $uploadedFile */
            $uploadedFile = $uploadedFiles['image'];
            
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                throw new BadRequestException('Upload failed with error code ' . $uploadedFile->getError());
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($uploadedFile->getClientMediaType(), $allowedTypes)) {
                throw new ValidationException('Validation failed', [
                    'image' => 'File must be an image (JPEG, PNG, or GIF)'
                ]);
            }
            
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8));
            $filename = sprintf('%s_%s.%s', $type, $basename, $extension);
            $directory = __DIR__ . '/../../public/uploads/businesses/' . $businessId;
            
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $uploadedFile->moveTo($directory . '/' . $filename);
            $imagePath = '/uploads/businesses/' . $businessId . '/' . $filename;
            $business->updateImage($type, $imagePath);
            
            $this->logger->info('Business image uploaded', [
                'business_id' => $business->id, 
                'type' => $type
            ]);
            
            return ResponseHelper::success($response, [
                'status' => 'success',
                'message' => ucfirst($type) . ' uploaded successfully',
                'data' => [$type => $imagePath]
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error("Error uploading {$type}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log a page view for analytics
     */
    private function logPageView(int $businessId): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO analytics_page_views 
                (business_id, ip_address, user_agent, referrer) 
                VALUES (:business_id, :ip_address, :user_agent, :referrer)"
            );
            
            $stmt->execute([
                ':business_id' => $businessId,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ':referrer' => $_SERVER['HTTP_REFERER'] ?? null
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to log page view', [
                'business_id' => $businessId,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Add rate limiting middleware
    private function checkRateLimit(string $ip, string $endpoint): bool {
        $key = "rate_limit:{$ip}:{$endpoint}";
        $limit = 100; // requests
        $window = 3600; // seconds
        
        // Implement rate limiting logic
        return true;
    }
}