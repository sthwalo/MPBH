<?php

namespace App\DTO;

use DateTime;

class BusinessDTO
{
    public ?int $id = null;
    public string $name;
    public string $category;
    public string $district;
    public ?string $description = null;
    public ?string $address = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $website = null;
    public ?string $logo = null;
    public ?string $cover_image = null;
    public string $package_type;
    public string $verification_status;
    public ?array $social_media = null;
    public ?string $business_hours = null;
    public ?float $longitude = null;
    public ?float $latitude = null;
    public ?DateTime $created_at = null;
    public ?DateTime $updated_at = null;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
