<?php

namespace App\Services\Interfaces;

use App\Models\Business;
use App\DTOs\BusinessDataTransfer;

interface BusinessServiceInterface
{
    public function create(BusinessDataTransfer $dto): Business;
    public function update(int $id, BusinessDataTransfer $dto): Business;
    public function delete(int $id): bool;
    public function getById(int $id): ?Business;
    public function getAll(array $filters = [], int $page = 1, int $limit = 20): array;
    public function verify(int $id): bool;
    public function getStats(int $id): array;
}
