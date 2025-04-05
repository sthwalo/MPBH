<?php

namespace App\Models;

use PDO;

class Business
{
    private PDO $db;
    
    // Database table name
    private string $table = 'businesses';
    
    // Business properties
    public ?int $business_id = null;
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public string $category;
    public string $district;
    public ?string $address = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $website = null;
    public ?string $logo = null;
    public ?string $cover_image = null;
    public string $package_type = 'Basic'; // One of: Basic, Bronze, Silver, Gold
    public ?int $subscription_id = null;
    public ?string $verification_status = 'pending';
    public ?string $social_media = null; // JSON
    public ?string $business_hours = null; // JSON
    public ?float $longitude = null;
    public ?float $latitude = null;
    public int $adverts_remaining = 0;
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
     * Create new business
     * 
     * @return bool Success status
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . "
                 (user_id, name, description, category, district, address, phone, email, website, package_type)
                 VALUES (:user_id, :name, :description, :category, :district, :address, :phone, :email, :website, :package_type)";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->district = htmlspecialchars(strip_tags($this->district));
        $this->address = $this->address ? htmlspecialchars(strip_tags($this->address)) : null;
        $this->phone = $this->phone ? htmlspecialchars(strip_tags($this->phone)) : null;
        $this->email = $this->email ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->website = $this->website ? htmlspecialchars(strip_tags($this->website)) : null;
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':package_type', $this->package_type);
        
        if ($stmt->execute()) {
            $this->business_id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get business by ID
     * 
     * @param int $id Business ID
     * @return bool Success status
     */
    public function readOne(int $id): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE business_id = :business_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':business_id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties
        $this->business_id = $row['business_id'];
        $this->user_id = $row['user_id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->category = $row['category'];
        $this->district = $row['district'];
        $this->address = $row['address'];
        $this->phone = $row['phone'];
        $this->email = $row['email'];
        $this->website = $row['website'];
        $this->logo = $row['logo'];
        $this->cover_image = $row['cover_image'];
        $this->package_type = $row['package_type'];
        $this->subscription_id = $row['subscription_id'];
        $this->verification_status = $row['verification_status'];
        $this->social_media = $row['social_media'];
        $this->business_hours = $row['business_hours'];
        $this->longitude = $row['longitude'];
        $this->latitude = $row['latitude'];
        $this->adverts_remaining = $row['adverts_remaining'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        
        return true;
    }
    
    /**
     * Get business by user ID
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function findByUserId(int $userId): bool
    {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Set properties with null coalescing to handle missing columns
        $this->business_id = $row['business_id'] ?? null; 
        $this->user_id = $row['user_id'];
        $this->name = $row['name'];
        $this->description = $row['description'] ?? null;
        $this->category = $row['category'];
        $this->district = $row['district'];
        $this->address = $row['address'] ?? null;
        $this->phone = $row['phone'] ?? null;
        $this->email = $row['email'];
        $this->website = $row['website'] ?? null;
        $this->logo = $row['logo'] ?? null;
        $this->cover_image = $row['cover_image'] ?? null;
        $this->package_type = $row['package_type'] ?? 'Basic';
        $this->subscription_id = $row['subscription_id'] ?? null;
        $this->verification_status = $row['verification_status'] ?? 'pending';
        $this->social_media = $row['social_media'] ?? null;
        $this->business_hours = $row['business_hours'] ?? null;
        $this->longitude = $row['longitude'] ?? null;
        $this->latitude = $row['latitude'] ?? null;
        $this->adverts_remaining = $row['adverts_remaining'] ?? 0;
        $this->created_at = $row['created_at'] ?? null;
        $this->updated_at = $row['updated_at'] ?? null;
        
        return true;
    }
    
    /**
     * Get all businesses with optional filtering
     * 
     * @param array $filters Search filters (category, district, search, etc.)
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $sortBy Field to sort by
     * @param string $order Sort order (asc/desc)
     * @return array Businesses data and pagination info
     */
    public function readAll(
        array $filters = [], 
        int $page = 1, 
        int $limit = 20, 
        string $sortBy = 'name', 
        string $order = 'asc'
    ): array {
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Base query
        $query = "SELECT * FROM " . $this->table;
        $countQuery = "SELECT COUNT(*) FROM " . $this->table;
        
        // Where conditions
        $conditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['category'])) {
            $conditions[] = "category = :category";
            $params[':category'] = $filters['category'];
        }
        
        if (!empty($filters['district'])) {
            $conditions[] = "district = :district";
            $params[':district'] = $filters['district'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $conditions[] = "(name LIKE :search OR description LIKE :search)";
            $params[':search'] = $searchTerm;
        }
        
        // Add where clause if conditions exist
        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
            $query .= $whereClause;
            $countQuery .= $whereClause;
        }
        
        // Add sorting
        $allowedSortFields = ['name', 'created_at', 'category', 'district'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'name';
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
        
        $query .= " ORDER BY {$sortBy} {$order}";
        
        // Add pagination
        $query .= " LIMIT :limit OFFSET :offset";
        
        // Prepare statements
        $stmt = $this->db->prepare($query);
        $countStmt = $this->db->prepare($countQuery);
        
        // Bind params for both queries
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
            $stmt->bindValue($key, $value);
        }
        
        // Bind pagination params
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        // Execute queries
        $countStmt->execute();
        $stmt->execute();
        
        // Get total records
        $totalRecords = (int) $countStmt->fetchColumn();
        
        // Fetch results
        $businesses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Clean up sensitive data for public display
            $business = $row;
            unset($business['user_id']); // Don't expose user_id publicly
            
            // Parse JSON fields
            $business['social_media'] = json_decode($row['social_media'] ?? null);
            $business['business_hours'] = json_decode($row['business_hours'] ?? null);
            
            $businesses[] = $business;
        }
        
        // Calculate pagination info
        $totalPages = ceil($totalRecords / $limit);
        
        return [
            'businesses' => $businesses,
            'pagination' => [
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Update business details
     * 
     * @return bool Success status
     */
    public function update(): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET name = :name, 
                     description = :description, 
                     category = :category,
                     district = :district,
                     address = :address,
                     phone = :phone,
                     email = :email,
                     website = :website,
                     social_media = :social_media,
                     business_hours = :business_hours,
                     longitude = :longitude,
                     latitude = :latitude
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->district = htmlspecialchars(strip_tags($this->district));
        $this->address = $this->address ? htmlspecialchars(strip_tags($this->address)) : null;
        $this->phone = $this->phone ? htmlspecialchars(strip_tags($this->phone)) : null;
        $this->email = $this->email ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->website = $this->website ? htmlspecialchars(strip_tags($this->website)) : null;
        
        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':social_media', $this->social_media);
        $stmt->bindParam(':business_hours', $this->business_hours);
        $stmt->bindParam(':longitude', $this->longitude);
        $stmt->bindParam(':latitude', $this->latitude);
        
        return $stmt->execute();
    }
    
    /**
     * Update business images (logo or cover)
     * 
     * @param string $type Image type ('logo' or 'cover_image')
     * @param string $filename Image filename
     * @return bool Success status
     */
    public function updateImage(string $type, string $filename): bool
    {
        if (!in_array($type, ['logo', 'cover_image'])) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . "
                 SET {$type} = :filename
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            // Update the property
            $this->$type = $filename;
            return true;
        }
        
        return false;
    }
    
    /**
     * Update business package type
     * 
     * @param string $packageType New package type
     * @param int|null $subscriptionId Subscription ID
     * @param int $advertsRemaining Number of adverts remaining
     * @return bool Success status
     * @throws \InvalidArgumentException If package type is invalid
     */
    public function updatePackage(string $packageType, ?int $subscriptionId, int $advertsRemaining): bool
    {
        // Validate package type
        $validPackages = ['Basic', 'Bronze', 'Silver', 'Gold'];
        if (!in_array($packageType, $validPackages)) {
            throw new \InvalidArgumentException("Invalid package type: $packageType. Must be one of: " . implode(', ', $validPackages));
        }
        
        // Set adverts remaining based on package type if not explicitly provided
        if ($advertsRemaining < 0) {
            switch ($packageType) {
                case 'Gold':
                    $advertsRemaining = 4;
                    break;
                case 'Silver':
                    $advertsRemaining = 1;
                    break;
                default:
                    $advertsRemaining = 0;
            }
        }
        
        $query = "UPDATE " . $this->table . "
                 SET package_type = :package_type, 
                     subscription_id = :subscription_id,
                     adverts_remaining = :adverts_remaining
                 WHERE business_id = :business_id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':package_type', $packageType);
        $stmt->bindParam(':subscription_id', $subscriptionId);
        $stmt->bindParam(':adverts_remaining', $advertsRemaining);
        $stmt->bindParam(':business_id', $this->business_id);
        
        if ($stmt->execute()) {
            $this->package_type = $packageType;
            $this->subscription_id = $subscriptionId;
            $this->adverts_remaining = $advertsRemaining;
            return true;
        }
        
        return false;
    }
    
    /**
     * Update adverts remaining count
     * 
     * @param int $count Number of adverts
     * @return bool Success status
     */
    public function updateAdvertsRemaining(int $count): bool
    {
        $query = "UPDATE " . $this->table . "
                 SET adverts_remaining = :count
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            $this->adverts_remaining = $count;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get statistics for business
     * 
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        // Views statistics
        $viewsQuery = "SELECT 
                           COUNT(*) as total,
                           COUNT(CASE WHEN viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
                           COUNT(CASE WHEN viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7_days
                       FROM analytics_page_views 
                       WHERE business_id = :id";
        
        $viewsStmt = $this->db->prepare($viewsQuery);
        $viewsStmt->bindParam(':id', $this->id);
        $viewsStmt->execute();
        $views = $viewsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Inquiries statistics
        $inquiriesQuery = "SELECT 
                              COUNT(*) as total,
                              COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
                              COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7_days
                           FROM analytics_inquiries 
                           WHERE business_id = :id";
        
        $inquiriesStmt = $this->db->prepare($inquiriesQuery);
        $inquiriesStmt->bindParam(':id', $this->id);
        $inquiriesStmt->execute();
        $inquiries = $inquiriesStmt->fetch(PDO::FETCH_ASSOC);
        
        // Reviews statistics
        $reviewsQuery = "SELECT 
                            COUNT(*) as total,
                            IFNULL(AVG(rating), 0) as average_rating,
                            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
                         FROM reviews 
                         WHERE business_id = :id AND status = 'approved'";
        
        $reviewsStmt = $this->db->prepare($reviewsQuery);
        $reviewsStmt->bindParam(':id', $this->id);
        $reviewsStmt->execute();
        $reviews = $reviewsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Format the data
        return [
            'views' => [
                'total' => (int) $views['total'],
                'last_30_days' => (int) $views['last_30_days'],
                'last_7_days' => (int) $views['last_7_days'],
            ],
            'inquiries' => [
                'total' => (int) $inquiries['total'],
                'last_30_days' => (int) $inquiries['last_30_days'],
                'last_7_days' => (int) $inquiries['last_7_days'],
            ],
            'reviews' => [
                'total' => (int) $reviews['total'],
                'average_rating' => (float) $reviews['average_rating'],
                'last_30_days' => (int) $reviews['last_30_days'],
            ]
        ];
    }
    
    /**
     * Get business data as array
     * 
     * @param bool $includePrivate Include private fields
     * @return array Business data
     */
    public function toArray(bool $includePrivate = false): array
    {
        $data = [
            'id' => $this->business_id, // Return as 'id' for backward compatibility
            'business_id' => $this->business_id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'district' => $this->district,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'package_type' => $this->package_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        
        // Include additional private fields for business owner
        if ($includePrivate) {
            $data['user_id'] = $this->user_id;
            $data['subscription_id'] = $this->subscription_id;
            $data['verification_status'] = $this->verification_status;
            $data['adverts_remaining'] = $this->adverts_remaining;
        }
        
        // Parse JSON fields
        $data['social_media'] = json_decode($this->social_media ?? '{}');
        $data['business_hours'] = json_decode($this->business_hours ?? '{}');
        
        return $data;
    }
}
