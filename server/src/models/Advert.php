<?php

namespace App\Models;

use PDO;

class Advert
{
    private PDO $db;
    
    // Database table name
    private string $table = 'adverts';
    
    // Advert properties
    public ?int $id = null;
    public int $business_id;
    public string $title;
    public ?string $description = null;
    public ?string $image = null;
    public ?string $url = null;
    public string $status = 'pending'; // pending, active, rejected, expired
    public string $placement = 'sidebar';
    public ?string $start_date = null;
    public ?string $end_date = null;
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
     * Create new advert
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (business_id, title, description, url, placement, start_date, end_date, status)
                 VALUES (:business_id, :title, :description, :url, :placement, :start_date, :end_date, :status)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->url = $this->url ? htmlspecialchars(strip_tags($this->url)) : null;
        
        // Bind parameters
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':url', $this->url);
        $stmt->bindParam(':placement', $this->placement);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get advert by ID
     * 
     * @param int $id Advert ID
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
        $this->title = $row['title'];
        $this->description = $row['description'];
        $this->image = $row['image'];
        $this->url = $row['url'];
        $this->status = $row['status'];
        $this->placement = $row['placement'];
        $this->start_date = $row['start_date'];
        $this->end_date = $row['end_date'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get adverts for a specific business
     * 
     * @param int $businessId Business ID
     * @param string $status Advert status filter
     * @return array Adverts array
     */
    public function getBusinessAdverts(int $businessId, string $status = 'all'): array
    {
        $query = "SELECT * FROM " . $this->table . "
                 WHERE business_id = :business_id";
        
        // Add status filter if provided
        if ($status !== 'all') {
            $query .= " AND status = :status";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $businessId);
        
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        
        $adverts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $adverts[] = $row;
        }
        
        return $adverts;
    }
    
    /**
     * Get active adverts for placement
     * 
     * @param string $placement Placement type
     * @param int $limit Maximum number of adverts
     * @return array Adverts array
     */
    public function getActiveAdvertsByPlacement(string $placement = 'sidebar', int $limit = 5): array
    {
        $query = "SELECT a.*, b.name as business_name, b.district 
                 FROM " . $this->table . " a
                 JOIN businesses b ON a.business_id = b.id
                 WHERE a.placement = :placement
                 AND a.status = 'active'
                 AND (a.start_date IS NULL OR a.start_date <= CURRENT_DATE())
                 AND (a.end_date IS NULL OR a.end_date >= CURRENT_DATE())
                 ORDER BY RAND() LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':placement', $placement);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $adverts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $adverts[] = $row;
        }
        
        return $adverts;
    }
    
    /**
     * Update advert details
     * 
     * @return bool Success status
     */
    public function update(): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET title = :title, 
                     description = :description, 
                     url = :url,
                     placement = :placement,
                     start_date = :start_date,
                     end_date = :end_date
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->url = $this->url ? htmlspecialchars(strip_tags($this->url)) : null;
        
        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':business_id', $this->business_id); // For security
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':url', $this->url);
        $stmt->bindParam(':placement', $this->placement);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        
        if ($stmt->execute()) {
            // Reset status to pending for review
            $this->updateStatus('pending');
            return true;
        }
        
        return false;
    }
    
    /**
     * Update advert status
     * 
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, ['pending', 'active', 'rejected', 'expired'])) {
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
     * Update advert image
     * 
     * @param string $filename Image filename
     * @return bool Success status
     */
    public function updateImage(string $filename): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET image = :filename
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':business_id', $this->business_id); // For security
        
        if ($stmt->execute()) {
            $this->image = $filename;
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete advert
     * 
     * @return bool Success status
     */
    public function delete(): bool
    {
        $query = "DELETE FROM " . $this->table . "
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':business_id', $this->business_id); // For security
        
        return $stmt->execute();
    }
    
    /**
     * Check if advert belongs to business
     * 
     * @param int $advertId Advert ID
     * @param int $businessId Business ID
     * @return bool Belongs to business
     */
    public function belongsToBusiness(int $advertId, int $businessId): bool
    {
        $query = "SELECT COUNT(*) FROM " . $this->table . "
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $advertId);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Update adverts that have passed their end date
     * 
     * @return int Number of adverts expired
     */
    public function expireOldAdverts(): int
    {
        $query = "UPDATE " . $this->table . "
                 SET status = 'expired'
                 WHERE status = 'active'
                 AND end_date IS NOT NULL
                 AND end_date < CURRENT_DATE()";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Get advert data as array
     * 
     * @return array Advert data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'url' => $this->url,
            'status' => $this->status,
            'placement' => $this->placement,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
