<?php

namespace App\Repositories\Business;

use App\Repositories\Interfaces\BusinessRepositoryInterface;
use App\Models\Business;

class BusinessRepository implements BusinessRepositoryInterface
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function create(Business $business): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO businesses (
                name, category, district, description, address,
                phone, email, website, logo, cover_image,
                package_type, verification_status, social_media,
                business_hours, longitude, latitude
            ) VALUES (
                :name, :category, :district, :description, :address,
                :phone, :email, :website, :logo, :cover_image,
                :package_type, :verification_status, :social_media,
                :business_hours, :longitude, :latitude
            )
        ");
        
        return $stmt->execute([
            'name' => $business->name,
            'category' => $business->category,
            'district' => $business->district,
            'description' => $business->description,
            'address' => $business->address,
            'phone' => $business->phone,
            'email' => $business->email,
            'website' => $business->website,
            'logo' => $business->logo,
            'cover_image' => $business->cover_image,
            'package_type' => $business->package_type,
            'verification_status' => $business->verification_status,
            'social_media' => $business->social_media,
            'business_hours' => $business->business_hours,
            'longitude' => $business->longitude,
            'latitude' => $business->latitude
        ]);
    }
    
    public function update(Business $business): bool
    {
        $stmt = $this->db->prepare("
            UPDATE businesses SET
                name = :name,
                category = :category,
                district = :district,
                description = :description,
                address = :address,
                phone = :phone,
                email = :email,
                website = :website,
                logo = :logo,
                cover_image = :cover_image,
                package_type = :package_type,
                verification_status = :verification_status,
                social_media = :social_media,
                business_hours = :business_hours,
                longitude = :longitude,
                latitude = :latitude
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $business->id,
            'name' => $business->name,
            'category' => $business->category,
            'district' => $business->district,
            'description' => $business->description,
            'address' => $business->address,
            'phone' => $business->phone,
            'email' => $business->email,
            'website' => $business->website,
            'logo' => $business->logo,
            'cover_image' => $business->cover_image,
            'package_type' => $business->package_type,
            'verification_status' => $business->verification_status,
            'social_media' => $business->social_media,
            'business_hours' => $business->business_hours,
            'longitude' => $business->longitude,
            'latitude' => $business->latitude
        ]);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM businesses WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function findById(int $id): ?Business
    {
        $stmt = $this->db->prepare("SELECT * FROM businesses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? new Business($row) : null;
    }
    
    public function findByEmail(string $email): ?Business
    {
        $stmt = $this->db->prepare("SELECT * FROM businesses WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? new Business($row) : null;
    }
    
    public function findBySlug(string $slug): ?Business
    {
        $stmt = $this->db->prepare("SELECT * FROM businesses WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? new Business($row) : null;
    }
    
    public function findAll(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $query = "SELECT * FROM businesses WHERE 1 = 1";
        $params = [];
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query .= " AND {$field} = :{$field}";
                $params[$field] = $value;
            }
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Business($row), $rows);
    }
    
    public function count(array $filters = []): int
    {
        $query = "SELECT COUNT(*) as count FROM businesses WHERE 1 = 1";
        $params = [];
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query .= " AND {$field} = :{$field}";
                $params[$field] = $value;
            }
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['count'] : 0;
    }
}
