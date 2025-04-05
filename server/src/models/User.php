<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;
    
    // Database table name
    private string $table = 'users';
    
    // User properties
    public ?int $user_id = null;
    public string $name;
    public string $email;
    public string $password; // Used for input only, stored as password_hash in DB
    public ?string $password_hash = null;
    public ?string $phone_number = null;
    public ?string $area_of_operation = null;
    public ?string $language_preference = null;
    
    /**
     * Constructor with database dependency
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create new user
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (name, email, password_hash, phone_number)
                 VALUES (:name, :email, :password_hash, :phone_number)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name ?? 'User'));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = password_hash($this->password, PASSWORD_DEFAULT); // Hash the password
        $this->phone_number = !empty($this->phone_number) ? htmlspecialchars(strip_tags($this->phone_number)) : null;
        
        // Bind params
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':phone_number', $this->phone_number);
        
        if ($stmt->execute()) {
            $this->user_id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function readOne(int $id): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->user_id = $row['user_id'];
        $this->name = $row['name'] ?? null;
        $this->email = $row['email'];
        $this->password_hash = $row['password_hash'];
        $this->phone_number = $row['phone_number'] ?? null;
        $this->area_of_operation = $row['area_of_operation'] ?? null;
        $this->language_preference = $row['language_preference'] ?? null;
        
        return true;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return bool Success status
     */
    public function findByEmail(string $email): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->user_id = $row['user_id'];
        $this->name = $row['name'] ?? null;
        $this->email = $row['email'];
        $this->password_hash = $row['password_hash'];
        $this->phone_number = $row['phone_number'] ?? null;
        $this->area_of_operation = $row['area_of_operation'] ?? null;
        $this->language_preference = $row['language_preference'] ?? null;
        
        return true;
    }
    
    /**
     * Update user
     * 
     * @return bool Success status
     */
    public function update(): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET email = :email, 
                     reset_token = :reset_token,
                     reset_token_expires = :reset_token_expires
                 WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize and bind params
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':reset_token', $this->reset_token);
        $stmt->bindParam(':reset_token_expires', $this->reset_token_expires);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update user password
     * 
     * @param string $password New password
     * @return bool Success status
     */
    public function updatePassword(string $password): bool
    {
        $query = "UPDATE " . $this->table . " 
                 SET password_hash = :password_hash 
                 WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password_hash', $hashedPassword);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Set password reset token
     * 
     * @param string $token Reset token
     * @param string $expires Expiration timestamp
     * @return bool Success status
     */
    public function setResetToken(string $token, string $expires): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET reset_token = :token, reset_token_expires = :expires
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->reset_token = $token;
            $this->reset_token_expires = $expires;
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by reset token
     * 
     * @param string $token Reset token
     * @return bool Success status
     */
    public function findByResetToken(string $token): bool
    {
        $query = "SELECT * FROM " . $this->table . "
                 WHERE reset_token = :token
                 AND reset_token_expires > NOW()
                 LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->user_id = $row['user_id'];
        $this->name = $row['name'] ?? null;
        $this->email = $row['email'];
        $this->password_hash = $row['password_hash'];
        $this->phone_number = $row['phone_number'] ?? null;
        $this->area_of_operation = $row['area_of_operation'] ?? null;
        $this->language_preference = $row['language_preference'] ?? null;
        
        return true;
    }
    
    /**
     * Verify user password
     * 
     * @param string $password Password to verify
     * @return bool Is password valid
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
    
    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @return bool Does email exist
     */
    public function emailExists(string $email): bool
    {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $email = strtolower(trim($email)); // Normalize email
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get user data as array (excluding password)
     * 
     * @return array User data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->user_id, // Return as 'id' for backward compatibility
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'area_of_operation' => $this->area_of_operation,
            'language_preference' => $this->language_preference
        ];
    }
    
    /**
     * Check if user needs to reset their password (older than 90 days)
     * 
     * @return bool True if password reset is needed
     */
    public function needsPasswordReset(): bool {
        // Check if last_password_change field exists
        if (!property_exists($this, 'last_password_change') || !$this->last_password_change) {
            return false; // Cannot determine if no date is set
        }
        
        // Get the date 90 days ago
        $ninetyDaysAgo = date('Y-m-d H:i:s', strtotime('-90 days'));
        
        // Compare with last password change date
        return $this->last_password_change < $ninetyDaysAgo;
    }
    
    /**
     * Update last password change timestamp
     * 
     * @return bool Success status
     */
    public function updatePasswordTimestamp(): bool {
        $query = "UPDATE {$this->table} SET updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$this->user_id]);
    }
}
