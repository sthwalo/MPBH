<?php

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Models\Business;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use PDO;

class AuthController
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
     * Register a new user and business
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function register(Request $request, Response $response): Response
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['email', 'password', 'businessName', 'category', 'district'];
        $errors = $this->validateRequiredFields($data, $requiredFields);
        
        // Validate email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Validate password length
        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Check if email already exists
        $user = new User($this->db);
        if ($user->emailExists($data['email'])) {
            throw new BadRequestException('Email already in use');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create user
            $user->email = $data['email'];
            $user->password = $data['password'];
            
            if (!$user->create()) {
                throw new \Exception('Failed to create user');
            }
            
            // Create business
            $business = new Business($this->db);
            $business->user_id = $user->id;
            $business->name = $data['businessName'];
            $business->description = $data['description'] ?? null;
            $business->category = $data['category'];
            $business->district = $data['district'];
            $business->address = $data['address'] ?? null;
            $business->phone = $data['phone'] ?? null;
            $business->email = $data['email']; // Use same email as user
            $business->website = $data['website'] ?? null;
            $business->package_type = $data['packageType'] ?? 'Basic';
            
            if (!$business->create()) {
                throw new \Exception('Failed to create business');
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Generate JWT token
            $token = $this->generateToken($user->id, $business->id);
            
            // Prepare response
            $responseData = [
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'token' => $token
                ]
            ];
            
            $this->logger->info('User registered', ['user_id' => $user->id, 'business_id' => $business->id]);
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Login user
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function login(Request $request, Response $response): Response
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['email', 'password'];
        $errors = $this->validateRequiredFields($data, $requiredFields);
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Find user by email
        $user = new User($this->db);
        if (!$user->findByEmail($data['email'])) {
            throw new AuthenticationException('Invalid credentials');
        }
        
        // Verify password
        if (!$user->verifyPassword($data['password'])) {
            $this->logger->warning('Failed login attempt', ['email' => $data['email']]);
            throw new AuthenticationException('Invalid credentials');
        }
        
        // Get associated business
        $business = new Business($this->db);
        if (!$business->findByUserId($user->id)) {
            throw new \Exception('Business not found for user');
        }
        
        // Generate JWT token
        $token = $this->generateToken($user->id, $business->id);
        
        // Prepare response
        $responseData = [
            'status' => 'success',
            'data' => [
                'user' => $user->toArray(),
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'package_type' => $business->package_type
                ],
                'token' => $token
            ]
        ];
        
        $this->logger->info('User logged in', ['user_id' => $user->id]);
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Logout user
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function logout(Request $request, Response $response): Response
    {
        // Note: Since we're using JWT, we don't need to do anything server-side
        // The client should remove the token from storage
        
        // We can log the logout event if user data is available
        $user = $request->getAttribute('user');
        if ($user) {
            $this->logger->info('User logged out', ['user_id' => $user->id]);
        }
        
        $responseData = [
            'status' => 'success',
            'message' => 'Logged out successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Refresh JWT token
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function refreshToken(Request $request, Response $response): Response
    {
        // Get current token from Authorization header
        $header = $request->getHeaderLine('Authorization');
        
        // Check if the Authorization header exists and has the Bearer prefix
        if (empty($header) || !preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            throw new AuthenticationException('Authentication token required');
        }
        
        $jwt = $matches[1];
        
        try {
            // Decode the JWT using the secret key
            $decoded = JWT::decode($jwt, $_ENV['JWT_SECRET'], ['HS256']);
            
            // Check if token is close to expiration (within 1 hour)
            $now = new \DateTimeImmutable();
            $expiration = $decoded->exp;
            
            // If token is not close to expiration, return the same token
            if ($expiration - $now->getTimestamp() > 3600) {
                $responseData = [
                    'status' => 'success',
                    'data' => [
                        'token' => $jwt,
                        'message' => 'Token still valid'
                    ]
                ];
                
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json');
            }
            
            // Generate new token
            $newToken = $this->generateToken($decoded->user_id, $decoded->business_id);
            
            $responseData = [
                'status' => 'success',
                'data' => [
                    'token' => $newToken
                ]
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid or expired token');
        }
    }
    
    /**
     * Send password reset link
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function forgotPassword(Request $request, Response $response): Response
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate email
        if (empty($data['email'])) {
            throw new ValidationException('Validation failed', ['email' => 'Email is required']);
        }
        
        // Find user by email
        $user = new User($this->db);
        if (!$user->findByEmail($data['email'])) {
            // Don't reveal that email doesn't exist for security reasons
            // Just pretend we sent an email
            $responseData = [
                'status' => 'success',
                'message' => 'If your email is registered, you will receive a password reset link.'
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = (new \DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
        
        // Save token to user
        $user->setResetToken($token, $expires);
        
        // In a real application, we would send an email with the reset link
        // For now, we'll just return the token in the response
        
        $this->logger->info('Password reset requested', ['user_id' => $user->id]);
        
        $responseData = [
            'status' => 'success',
            'message' => 'If your email is registered, you will receive a password reset link.'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Reset password using token
     * 
     * @param Request $request Request object
     * @param Response $response Response object
     * @return Response JSON response
     */
    public function resetPassword(Request $request, Response $response): Response
    {
        // Get request data
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['token', 'password', 'confirm_password'];
        $errors = $this->validateRequiredFields($data, $requiredFields);
        
        // Validate password
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
            
            if ($data['password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
        
        // If validation errors, return 422 response
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
        
        // Find user by reset token
        $user = new User($this->db);
        if (!$user->findByResetToken($data['token'])) {
            throw new BadRequestException('Invalid or expired token');
        }
        
        // Update password
        if (!$user->updatePassword($data['password'])) {
            throw new \Exception('Failed to update password');
        }
        
        $this->logger->info('Password reset completed', ['user_id' => $user->id]);
        
        $responseData = [
            'status' => 'success',
            'message' => 'Password has been reset successfully'
        ];
        
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Generate JWT token
     * 
     * @param int $userId User ID
     * @param int $businessId Business ID
     * @return string JWT token
     */
    private function generateToken(int $userId, int $businessId): string
    {
        $issuedAt = new \DateTimeImmutable();
        $expiry = $issuedAt->modify('+' . $_ENV['JWT_EXPIRY'] . ' seconds')->getTimestamp();
        
        $data = [
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiry,
            'user_id' => $userId,
            'business_id' => $businessId
        ];
        
        return JWT::encode($data, $_ENV['JWT_SECRET'], 'HS256');
    }
    
    /**
     * Validate required fields
     * 
     * @param array $data Request data
     * @param array $required Required field names
     * @return array Validation errors
     */
    private function validateRequiredFields(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        return $errors;
    }
}
