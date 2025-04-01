<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;
    
    // Database table name
    private string $table = 'users';
    
    // User properties
    public ?int $id = null;
    public string $email;
    public string $password;
    public ?string $reset_token = null;
    public ?string $reset_token_expires = null;
    public string $created_at;
    public ?string $updated_at = null;
    
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
                 (email, password)
                 VALUES (:email, :password)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize and bind params
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT); // Hash the password
        
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
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
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->id = $row['id'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->reset_token = $row['reset_token'];
        $this->reset_token_expires = $row['reset_token_expires'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
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
        $this->id = $row['id'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->reset_token = $row['reset_token'];
        $this->reset_token_expires = $row['reset_token_expires'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
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
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize and bind params
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':reset_token', $this->reset_token);
        $stmt->bindParam(':reset_token_expires', $this->reset_token_expires);
        $stmt->bindParam(':id', $this->id);
        
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
                 SET password = :password, 
                     reset_token = NULL, 
                     reset_token_expires = NULL 
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $this->id);
        
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
        $this->id = $row['id'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->reset_token = $row['reset_token'];
        $this->reset_token_expires = $row['reset_token_expires'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
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
        return password_verify($password, $this->password);
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
            'id' => $this->id,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
