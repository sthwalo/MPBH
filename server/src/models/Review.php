<?php

namespace App\Models;

use PDO;

class Review
{
    private PDO $db;
    
    // Database table name
    private string $table = 'reviews';
    
    // Review properties
    public ?int $id = null;
    public int $business_id;
    public int $user_id;
    public string $reviewer_name;
    public float $rating;
    public string $comment;
    public string $status = 'pending'; // pending, approved, rejected
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
     * Create new review
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (business_id, user_id, reviewer_name, rating, comment, status)
                 VALUES (:business_id, :user_id, :reviewer_name, :rating, :comment, :status)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->reviewer_name = htmlspecialchars(strip_tags($this->reviewer_name));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        
        // Bind parameters
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':reviewer_name', $this->reviewer_name);
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':comment', $this->comment);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get review by ID
     * 
     * @param int $id Review ID
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
        $this->business_id = $row['business_id'];
        $this->user_id = $row['user_id'];
        $this->reviewer_name = $row['reviewer_name'];
        $this->rating = $row['rating'];
        $this->comment = $row['comment'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get reviews for a specific business
     * 
     * @param int $businessId Business ID
     * @param string $status Review status filter
     * @param int $limit Number of reviews to return
     * @param int $offset Offset for pagination
     * @return array Reviews array
     */
    public function getBusinessReviews(
        int $businessId, 
        string $status = 'approved', 
        int $limit = 10, 
        int $offset = 0
    ): array {
        $query = "SELECT 
                     r.*,
                     IFNULL(u.name, 'Anonymous') as reviewer_name
                 FROM " . $this->table . " r
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE r.business_id = :business_id";
        
        // Add status filter if provided
        if ($status !== 'all') {
            $query .= " AND r.status = :status";
        }
        
        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $reviews = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Remove user_id for privacy
            unset($row['user_id']);
            $reviews[] = $row;
        }
        
        return $reviews;
    }
    
    /**
     * Get reviews by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Number of reviews to return
     * @param int $offset Offset for pagination
     * @return array Reviews array
     */
    public function getUserReviews(int $userId, int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT 
                     r.*,
                     b.name as business_name
                 FROM " . $this->table . " r
                 JOIN businesses b ON r.business_id = b.id
                 WHERE r.user_id = :user_id
                 ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $reviews = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reviews[] = $row;
        }
        
        return $reviews;
    }
    
    /**
     * Update review status
     * 
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . "
                 SET status = :status
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->status = $status;
            return true;
        }
        
        return false;
    }
    
    /**
     * Update review content
     * 
     * @return bool Success status
     */
    public function update(): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET rating = :rating, 
                     comment = :comment
                 WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize input
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        
        // Bind parameters
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':comment', $this->comment);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id); // For security
        
        return $stmt->execute();
    }
    
    /**
     * Delete review
     * 
     * @return bool Success status
     */
    public function delete(): bool
    {
        $query = "DELETE FROM " . $this->table . "
                 WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id); // For security
        
        return $stmt->execute();
    }
    
    /**
     * Check if user has already reviewed a business
     * 
     * @param int $userId User ID
     * @param int $businessId Business ID
     * @return bool Has reviewed
     */
    public function hasUserReviewed(int $userId, int $businessId): bool
    {
        $query = "SELECT COUNT(*) FROM " . $this->table . "
                 WHERE user_id = :user_id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get review data as array
     * 
     * @return array Review data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'reviewer_name' => $this->reviewer_name,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
