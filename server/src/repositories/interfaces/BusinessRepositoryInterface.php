<?php

namespace App\Repositories\Interfaces;

use App\Models\Business;

interface BusinessRepositoryInterface
{
    public function create(Business $business): bool;
    public function update(Business $business): bool;
    public function delete(int $id): bool;
    public function findById(int $id): ?Business;
    public function findByEmail(string $email): ?Business;
    public function findBySlug(string $slug): ?Business;
    public function findAll(array $filters = [], int $page = 1, int $limit = 20): array;
    public function count(array $filters = []): int;
}
