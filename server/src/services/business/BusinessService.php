<?php

namespace App\Services\Business;

use App\Services\Interfaces\BusinessServiceInterface;
use App\Repositories\Interfaces\BusinessRepositoryInterface;
use App\DTOs\BusinessDataTransfer;
use App\Models\Business;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use DateTime;

class BusinessService implements BusinessServiceInterface
{
    private BusinessRepositoryInterface $repository;
    private BusinessValidator $validator;
    
    public function __construct(
        BusinessRepositoryInterface $repository,
        BusinessValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }
    
    public function getAll(array $filters = [], int $page = 1, int $limit = 20): array
    {
        return $this->repository->getAll($filters, $page, $limit);
    }
    
    public function getById(int $id): ?Business
    {
        return $this->repository->getById($id);
    }
    
    public function create(BusinessDTO $dto): Business
    {
        $this->validator->validate($dto);
        $business = new Business();
        $business->name = $dto->name;
        $business->category = $dto->category;
        $business->district = $dto->district;
        $business->description = $dto->description;
        $business->address = $dto->address;
        $business->phone = $dto->phone;
        $business->email = $dto->email;
        $business->website = $dto->website;
        $business->logo = $dto->logo;
        $business->cover_image = $dto->cover_image;
        $business->package_type = $dto->package_type;
        $business->verification_status = $dto->verification_status;
        $business->social_media = $dto->social_media;
        $business->business_hours = $dto->business_hours;
        $business->longitude = $dto->longitude;
        $business->latitude = $dto->latitude;
        
        $this->repository->create($business);
        return $business;
    }
    
    public function update(int $id, BusinessDTO $dto): Business
    {
        $business = $this->repository->getById($id);
        if (!$business) {
            throw new NotFoundException('Business not found');
        }
        
        $this->validator->validate($dto);
        
        // Update only the fields that were provided
        foreach ($dto as $key => $value) {
            if ($value !== null) {
                $business->$key = $value;
            }
        }
        
        $this->repository->update($business);
        return $business;
    }
    
    public function delete(int $id): bool
    {
        $business = $this->repository->getById($id);
        if (!$business) {
            throw new NotFoundException('Business not found');
        }
        
        return $this->repository->delete($id);
    }
    
    public function getStats(int $id): array
    {
        $business = $this->repository->getById($id);
        if (!$business) {
            throw new NotFoundException('Business not found');
        }
        
        return [
            'total_reviews' => $business->getReviews()->count(),
            'average_rating' => $business->getAverageRating(),
            'total_products' => $business->getProducts()->count(),
            'total_views' => $business->views,
            'created_at' => $business->created_at->format('Y-m-d'),
            'last_updated' => $business->updated_at->format('Y-m-d')
        ];
    }
    
    public function verify(int $id): Business
    {
        $business = $this->repository->getById($id);
        if (!$business) {
            throw new NotFoundException('Business not found');
        }
        
        $business->verification_status = 'verified';
        $business->verified_at = new DateTime();
        
        $this->repository->update($business);
        return $business;
    }
}
