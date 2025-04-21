<?php

namespace App\Services;

use App\Exceptions\RateLimitException;

class RateLimiter
{
    private const DEFAULT_LIMIT = 100; // requests
    private const DEFAULT_WINDOW = 3600; // seconds
    
    public function __construct(
        private \Redis $redis
    ) {}
    
    public function check(string $ip, string $endpoint, int $limit = null, int $window = null): void
    {
        $limit = $limit ?? self::DEFAULT_LIMIT;
        $window = $window ?? self::DEFAULT_WINDOW;
        
        $key = "rate_limit:{$ip}:{$endpoint}";
        $current = (int)$this->redis->get($key) ?? 0;
        
        if ($current >= $limit) {
            throw new RateLimitException(
                "Rate limit exceeded. Please wait before making more requests.",
                $window
            );
        }
        
        $this->redis->incr($key);
        $this->redis->expire($key, $window);
    }
}
