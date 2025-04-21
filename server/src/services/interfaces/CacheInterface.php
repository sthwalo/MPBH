<?php

namespace App\Services\Interfaces;

interface CacheInterface
{
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function get(string $key): mixed;
    public function delete(string $key): bool;
    public function has(string $key): bool;
    public function increment(string $key, int $step = 1): int;
    public function decrement(string $key, int $step = 1): int;
}
