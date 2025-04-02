<?php

namespace App\Services;

use PDO;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Exceptions\AuthenticationException;

class AuthService {
    private $db;
    private $user;
    private $emailService;
    
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->emailService = new EmailService();
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User registration data
     * @return array Registered user details with token
     */
    public function register(array $data): array {
        // Validate email uniqueness
        if ($this->user->getUserByEmail($data['email'])) {
            throw new \Exception('Email already registered');
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Create user
        $userId = $this->user->createUser($data);
        $user = $this->user->getUserById($userId);
        
        // Send welcome email
        $this->emailService->sendWelcomeEmail($user['email'], $user['email']);
        
        // Generate token
        $token = $this->generateToken($user);
        
        // Return user data with token
        unset($user['password']);
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Authenticated user details with token
     */
    public function login(string $email, string $password): array {
        // Get user by email
        $user = $this->user->getUserByEmail($email);
        if (!$user) {
            throw new AuthenticationException('Invalid email or password');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new AuthenticationException('Invalid email or password');
        }
        
        // Generate token
        $token = $this->generateToken($user);
        
        // Return user data with token
        unset($user['password']);
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    /**
     * Generate a password reset token
     * 
     * @param string $email User email
     * @return bool Success status
     */
    public function requestPasswordReset(string $email): bool {
        // Get user by email
        $user = $this->user->getUserByEmail($email);
        if (!$user) {
            // For security, don't reveal that email doesn't exist
            return false;
        }
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update user with reset token
        $this->user->updateUser($user['id'], [
            'reset_token' => $resetToken,
            'reset_token_expires' => $tokenExpires
        ]);
        
        // Send password reset email
        return $this->emailService->sendPasswordResetEmail($email, $resetToken, $user['email']);
    }
    
    /**
     * Reset user password using token
     * 
     * @param string $email User email
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool {
        // Get user by email
        $user = $this->user->getUserByEmail($email);
        if (!$user) {
            throw new \Exception('Invalid email');
        }
        
        // Verify token
        if ($user['reset_token'] !== $token) {
            throw new \Exception('Invalid reset token');
        }
        
        // Check if token is expired
        if (strtotime($user['reset_token_expires']) < time()) {
            throw new \Exception('Reset token expired');
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user
        return $this->user->updateUser($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }
    
    /**
     * Refresh authentication token
     * 
     * @param int $userId User ID
     * @return string New token
     */
    public function refreshToken(int $userId): string {
        // Get user by ID
        $user = $this->user->getUserById($userId);
        if (!$user) {
            throw new AuthenticationException('User not found');
        }
        
        // Generate new token
        return $this->generateToken($user);
    }
    
    /**
     * Generate JWT token for user
     * 
     * @param array $user User data
     * @return string JWT token
     */
    private function generateToken(array $user): string {
        $payload = [
            'sub' => $user['id'], // Subject (user ID)
            'email' => $user['email'],
            'iat' => time(), // Issued at
            'exp' => time() + (intval($_ENV['JWT_EXPIRY'] ?? 3600)) // Expiration
        ];
        
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }
    
    /**
     * Validate and decode a JWT token
     * 
     * @param string $token JWT token
     * @return array Decoded token payload
     */
    public function validateToken(string $token): array {
        try {
            return (array) JWT::decode($token, $_ENV['JWT_SECRET'], ['HS256']);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid token: ' . $e->getMessage());
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool {
        // Get user
        $user = $this->user->getUserById($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            throw new \Exception('Current password is incorrect');
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user
        return $this->user->updateUser($userId, ['password' => $hashedPassword]);
    }
}
