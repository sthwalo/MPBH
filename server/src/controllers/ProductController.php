<?php

namespace App\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Business;
use App\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Monolog\Logger;
use PDO;

class ProductController
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
     * Get products for authenticated business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function getMyProducts(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if business has products feature
        if (!in_array($business->package_type, ['Silver', 'Gold'])) {
            throw new AuthorizationException('Your package does not allow product management');
        }
        
        // Get status filter
        $status = $request->getQueryParams()['status'] ?? 'all';
        
        // Get products
        $product = new Product($this->db);
        $products = $product->getBusinessProducts($businessId, $status);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $products
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get specific product
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function getProduct(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get product
        $product = new Product($this->db);
        if (!$product->readOne($id)) {
            throw new NotFoundException('Product not found');
        }
        
        // Verify ownership
        if ($product->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to view this product');
        }
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => $product->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Create a new product
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function createProduct(Request $request, Response $response): Response
    {
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get business
        $business = new Business($this->db);
        if (!$business->readOne($businessId)) {
            throw new NotFoundException('Business not found');
        }
        
        // Check if business has products feature
        if (!in_array($business->package_type, ['Silver', 'Gold'])) {
            throw new AuthorizationException('Your package does not allow product management');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['name'];
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        // Validate price if provided
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $errors['price'] = 'Price must be a positive number';
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Create product
        $product = new Product($this->db);
        $product->business_id = $businessId;
        $product->name = $data['name'];
        $product->description = $data['description'] ?? null;
        $product->price = isset($data['price']) ? (float) $data['price'] : null;
        $product->status = $data['status'] ?? 'active';
        
        if (!$product->create()) {
            throw new \Exception('Failed to create product');
        }
        
        $this->logger->info('Product created', ['product_id' => $product->id, 'business_id' => $businessId]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
    
    /**
     * Update a product
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function updateProduct(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get product
        $product = new Product($this->db);
        if (!$product->readOne($id)) {
            throw new NotFoundException('Product not found');
        }
        
        // Verify ownership
        if ($product->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to update this product');
        }
        
        // Get request data
        $data = $request->getParsedBody();
        
        // Update only the fields that are allowed to be updated
        if (isset($data['name'])) $product->name = $data['name'];
        if (isset($data['description'])) $product->description = $data['description'];
        if (isset($data['price'])) {
            // Validate price
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                throw new ValidationException('Validation failed', ['price' => 'Price must be a positive number']);
            }
            $product->price = (float) $data['price'];
        }
        if (isset($data['status'])) {
            // Validate status
            if (!in_array($data['status'], ['active', 'inactive'])) {
                throw new ValidationException('Validation failed', ['status' => 'Invalid status value']);
            }
            $product->status = $data['status'];
        }
        
        // Update product
        if (!$product->update()) {
            throw new \Exception('Failed to update product');
        }
        
        $this->logger->info('Product updated', ['product_id' => $product->id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product->toArray()
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Delete a product
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function deleteProduct(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get product
        $product = new Product($this->db);
        if (!$product->readOne($id)) {
            throw new NotFoundException('Product not found');
        }
        
        // Verify ownership
        if ($product->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to delete this product');
        }
        
        // Delete product
        if (!$product->delete()) {
            throw new \Exception('Failed to delete product');
        }
        
        $this->logger->info('Product deleted', ['product_id' => $id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Upload product image
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @param array $args Route arguments
     * @return Response JSON response
     */
    public function uploadProductImage(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Get authenticated user from token
        $userData = $request->getAttribute('user');
        $businessId = $userData->business_id;
        
        // Get product
        $product = new Product($this->db);
        if (!$product->readOne($id)) {
            throw new NotFoundException('Product not found');
        }
        
        // Verify ownership
        if ($product->business_id !== $businessId) {
            throw new AuthorizationException('You do not have permission to update this product');
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
        $filename = sprintf('product_%s.%s', $basename, $extension);
        
        // Create uploads directory if it doesn't exist
        $directory = __DIR__ . '/../../public/uploads/products/' . $businessId;
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Move the uploaded file to the uploads directory
        $uploadedFile->moveTo($directory . '/' . $filename);
        
        // Update product with new image path
        $imagePath = '/uploads/products/' . $businessId . '/' . $filename;
        $product->updateImage($imagePath);
        
        $this->logger->info('Product image uploaded', ['product_id' => $product->id]);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Product image uploaded successfully',
            'data' => [
                'image' => $imagePath
            ]
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
