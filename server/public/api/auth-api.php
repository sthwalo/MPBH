<?php
// Real API implementation for auth and business operations

// Set CORS headers for all requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get database connection and models
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use App\Models\Business;

try {
    // Connect to database
    $dbConfig = require __DIR__ . '/../../src/config/database-config.php';
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}",
        $dbConfig['username'],
        $dbConfig['password'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Get the request URI path
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Get the raw input data
    $rawData = file_get_contents('php://input');
    $jsonData = json_decode($rawData, true);
    
    // Log request for debugging
    file_put_contents('api_requests.log', date('Y-m-d H:i:s') . ' - ' . $path . ' - ' . $rawData . "\n", FILE_APPEND);

    // REGISTRATION ENDPOINT
    if (strpos($path, '/api/auth/register') !== false) {
        handleRegistration($db, $jsonData);
    } 
    // BUSINESS DETAILS ENDPOINT
    elseif (strpos($path, '/api/business/details') !== false) {
        handleBusinessDetails($db);
    } 
    // USER PROFILE ENDPOINT
    elseif (strpos($path, '/api/user/profile') !== false) {
        handleUserProfile($db);
    }
    // FALLBACK - Return 404 for unknown routes
    else {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Endpoint not found'
        ]);
    }
} catch (Exception $e) {
    // Log the error
    file_put_contents('api_errors.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return a generic error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error occurred',
        'debug' => $e->getMessage() // Remove in production
    ]);
}

/**
 * Handle user registration and business creation
 */
function handleRegistration(PDO $db, array $data): void {
    // Validate required fields
    $requiredFields = ['email', 'password', 'businessName', 'category', 'district'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => [$field => "$field is required"]
            ]);
            exit;
        }
    }
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Create user
        $user = new User($db);
        
        // Check if email already exists
        if ($user->emailExists($data['email'])) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'Email already exists'
            ]);
            exit;
        }
        
        // Set user properties
        $user->email = $data['email'];
        $user->password = $data['password']; // Will be hashed in create method
        
        // Create the user
        if (!$user->create()) {
            throw new Exception("Failed to create user");
        }
        
        // Create business
        $business = new Business($db);
        $business->user_id = $user->id;
        $business->name = $data['businessName'];
        $business->description = $data['description'] ?? null;
        $business->category = $data['category'];
        $business->district = $data['district'];
        $business->address = $data['address'] ?? null;
        $business->phone = $data['phone'] ?? null;
        $business->email = $data['email'];
        $business->website = $data['website'] ?? null;
        
        // Create the business
        if (!$business->create()) {
            throw new Exception("Failed to create business");
        }
        
        // Commit transaction
        $db->commit();
        
        // Generate JWT token (simplified)
        $token = generateJWT($user->id);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'user_id' => $user->id,
                'business_id' => $business->id,
                'token' => $token
            ]
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw $e;
    }
}

/**
 * Handle business details request
 */
function handleBusinessDetails(PDO $db): void {
    // Check for Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Missing or invalid token'
        ]);
        exit;
    }
    
    // Extract token
    $token = substr($authHeader, 7);
    
    try {
        // Verify token and get user ID
        $userId = verifyJWT($token);
        
        // Get business data
        $business = new Business($db);
        if (!$business->findByUserId($userId)) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Business not found'
            ]);
            exit;
        }
        
        // Return business data
        echo json_encode([
            'status' => 'success',
            'data' => $business->toArray(true)
        ]);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token'
        ]);
    }
}

/**
 * Handle user profile request
 */
function handleUserProfile(PDO $db): void {
    // Check for Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Missing or invalid token'
        ]);
        exit;
    }
    
    // Extract token
    $token = substr($authHeader, 7);
    
    try {
        // Verify token and get user ID
        $userId = verifyJWT($token);
        
        // Get user data
        $user = new User($db);
        if (!$user->readOne($userId)) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'User not found'
            ]);
            exit;
        }
        
        // Return user data
        echo json_encode([
            'status' => 'success',
            'data' => $user->toArray()
        ]);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token'
        ]);
    }
}

/**
 * Generate a JWT token (simplified version for demo purposes)
 */
function generateJWT(int $userId): string {
    // In a real app, this would use proper JWT libraries and secret keys
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 hours
    ]));
    
    // In a real app, you'd use a secure secret and proper signing
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", 'mpbh_secret_key', true));
    
    return "$header.$payload.$signature";
}

/**
 * Verify a JWT token (simplified)
 */
function verifyJWT(string $token): int {
    $parts = explode('.', $token);
    
    if (count($parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    
    // For the simplified approach, we're just extracting the user ID
    // A real implementation would validate signature and check expiration
    $payload = json_decode(base64_decode($parts[1]), true);
    
    if (!isset($payload['sub']) || !is_numeric($payload['sub'])) {
        throw new Exception('Invalid token payload');
    }
    
    return (int) $payload['sub'];
}
