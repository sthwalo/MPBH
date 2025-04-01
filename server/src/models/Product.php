<?php

namespace App\Models;

use PDO;

class Product
{
    private PDO $db;
    
    // Database table name
    private string $table = 'products';
    
    // Product properties
    public ?int $id = null;
    public int $business_id;
    public string $name;
    public ?string $description = null;
    public ?float $price = null;
    public ?string $image = null;
    public string $status = 'active';
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
     * Create new product
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (business_id, name, description, price, status)
                 VALUES (:business_id, :name, :description, :price, :status)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        
        // Bind parameters
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get product by ID
     * 
     * @param int $id Product ID
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
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->price = $row['price'];
        $this->image = $row['image'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get products for a specific business
     * 
     * @param int $businessId Business ID
     * @param string $status Product status filter
     * @return array Products array
     */
    public function getBusinessProducts(int $businessId, string $status = 'active'): array
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
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Update product details
     * 
     * @return bool Success status
     */
    public function update(): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET name = :name, 
                     description = :description, 
                     price = :price,
                     status = :status
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        
        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':business_id', $this->business_id); // For security
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }
    
    /**
     * Update product image
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
     * Delete product
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
     * Check if product belongs to business
     * 
     * @param int $productId Product ID
     * @param int $businessId Business ID
     * @return bool Belongs to business
     */
    public function belongsToBusiness(int $productId, int $businessId): bool
    {
        $query = "SELECT COUNT(*) FROM " . $this->table . "
                 WHERE id = :id AND business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $productId);
        $stmt->bindParam(':business_id', $businessId);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get product data as array
     * 
     * @return array Product data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->image,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
