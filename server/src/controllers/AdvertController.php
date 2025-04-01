<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Business;
use App\Models\Advert;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Monolog\Logger;
use PDO;

class AdvertController
{
    private PDO $db;
    private Logger $logger;
    
    /**
     * Constructor with dependencies
     * 
     * @param PDO $db Database connection
     * @param Logger $logger Logger instance
     */
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * Get adverts for authenticated business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getMyAdverts(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Get status filter
        $status = $request->getQueryParams()['status'] ?? 'all';
        
        // Get adverts
        $advert = new Advert($this->db);
        $adverts = $advert->getBusinessAdverts($businessId, $status);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => [
                'adverts' => $adverts,
                'remaining_slots' => $business->adverts_remaining
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get specific advert
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function getAdvert(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get advert
        $advert = new Advert($this->db);
        if (!$advert->readOne($id)) {
            throw new NotFoundException('Advert not found');
        }
        
        // Verify ownership
        if ($advert->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to view this advert');
        }
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $advert->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Create a new advert
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function createAdvert(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if business has adverts remaining
        if ($business->adverts_remaining <= 0) {
            throw new AuthorizationException('No advert slots remaining. Please upgrade your package.');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['title', 'placement'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        // Validate placement
        if (isset($data['placement']) && !in_array($data['placement'], ['sidebar', 'banner', 'featured'])) {
            $errors['placement'] = 'Invalid placement value';
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Create advert
        $advert = new Advert($this->db);
        $advert->business_id = $businessId;
        $advert->title = $data['title'];
        $advert->description = $data['description'] ?? null;
        $advert->url = $data['url'] ?? null;
        $advert->placement = $data['placement'];
        $advert->start_date = $data['start_date'] ?? null;
        $advert->end_date = $data['end_date'] ?? null;
        $advert->status = 'pending'; // All new adverts start as pending
        
        try {
            $this->db->beginTransaction();
            
            // Create advert
            if (!$advert->create()) {
                throw new \Exception('Failed to create advert');
            }
            
            // Decrease adverts remaining
            $business->updateAdvertsRemaining($business->adverts_remaining - 1);
            
            $this->db->commit();
            
            $this->logger->info('Advert created', ['advert_id' => $advert->id, 'business_id' => $businessId]);
            
            // Prepare response
            $responseData = [
                'status' => 'success',
                'message' => 'Advert created successfully. It will be published after approval.',
                'data' => [
                    'advert' => $advert->toArray(),
                    'remaining_slots' => $business->adverts_remaining
                ]
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Update an advert
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function updateAdvert(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get advert
        $advert = new Advert($this->db);
        if (!$advert->readOne($id)) {
            throw new NotFoundException('Advert not found');
        }
        
        // Verify ownership
        if ($advert->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to update this advert');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Update only the fields that are allowed to be updated
        if (isset($data['title'])) $advert->title = $data['title'];
        if (isset($data['description'])) $advert->description = $data['description'];
        if (isset($data['url'])) $advert->url = $data['url'];
        if (isset($data['placement'])) {
            // Validate placement
            if (!in_array($data['placement'], ['sidebar', 'banner', 'featured'])) {
                throw new ValidationException('Validation failed', ['placement' => 'Invalid placement value']);
            }
            $advert->placement = $data['placement'];
        }
        if (isset($data['start_date'])) $advert->start_date = $data['start_date'];
        if (isset($data['end_date'])) $advert->end_date = $data['end_date'];
        
        // Update advert
        if (!$advert->update()) {
            throw new \Exception('Failed to update advert');
        }
        
        $this->logger->info('Advert updated', ['advert_id' => $advert->id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Advert updated successfully. It will be published after approval.',
            'data' => $advert->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Delete an advert
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function deleteAdvert(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get advert
        $advert = new Advert($this->db);
        if (!$advert->readOne($id)) {
            throw new NotFoundException('Advert not found');
        }
        
        // Verify ownership
        if ($advert->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to delete this advert');
        }
        
        // Delete advert
        if (!$advert->delete()) {
            throw new \Exception('Failed to delete advert');
        }
        
        $this->logger->info('Advert deleted', ['advert_id' => $id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Advert deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Upload advert image
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function uploadImage(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get advert
        $advert = new Advert($this->db);
        if (!$advert->readOne($id)) {
            throw new NotFoundException('Advert not found');
        }
        
        // Verify ownership
        if ($advert->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to update this advert');
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
        $filename = sprintf('advert_%s.%s', $basename, $extension);
        
        // Create uploads directory if it doesn't exist
        $directory = __DIR__ . '/../../public/uploads/adverts/' . $businessId;
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Move the uploaded file to the uploads directory
        $uploadedFile->moveTo($directory . '/' . $filename);
        
        // Update advert with new image path
        $imagePath = '/uploads/adverts/' . $businessId . '/' . $filename;
        $advert->updateImage($imagePath);
        
        $this->logger->info('Advert image uploaded', ['advert_id' => $advert->id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Advert image uploaded successfully',
            'data' => [
                'image' => $imagePath
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get all active adverts for a specific placement (public route)
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function getActiveAdverts(Request $request, Response $response, array $args): Response
    {
        // Get placement from URL parameter
        $placement = $args['placement'] ?? 'sidebar';
        
        // Validate placement
        if (!in_array($placement, ['sidebar', 'banner', 'featured'])) {
            throw new BadRequestException('Invalid placement parameter');
        }
        
        // Get limit from query params
        $limit = isset($request->getQueryParams()['limit']) ? (int) $request->getQueryParams()['limit'] : 5;
        if ($limit < 1 || $limit > 20) $limit = 5; // Validate limit
        
        // Get adverts
        $advert = new Advert($this->db);
        $adverts = $advert->getActiveAdvertsByPlacement($placement, $limit);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $adverts
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
