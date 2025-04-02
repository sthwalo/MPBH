<?php

namespace App\Cache;

use Redis;
use Exception;

class RedisCache {
    private $client;
    private $enabled;

    /**
     * Initialize Redis cache connection
     * Falls back gracefully if Redis is not available
     */
    public function __construct() {
        $this->enabled = extension_loaded('redis') && 
                        !empty($_ENV['REDIS_HOST']) && 
                        !empty($_ENV['REDIS_PORT']);

        if ($this->enabled) {
            try {
                $this->client = new Redis();
                $this->client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
                
                // Set password if configured
                if (!empty($_ENV['REDIS_PASSWORD'])) {
                    $this->client->auth($_ENV['REDIS_PASSWORD']);
                }
                
                // Select database if configured
                if (isset($_ENV['REDIS_DB'])) {
                    $this->client->select((int)$_ENV['REDIS_DB']);
                }
            } catch (Exception $e) {
                // Disable Redis if connection fails
                $this->enabled = false;
                error_log('Redis connection failed: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Cache and return the result of a callback function
     * Falls back to direct execution if Redis is unavailable
     * 
     * @param string $key Cache key
     * @param int $ttl Time-to-live in seconds
     * @param callable $callback Function to execute and cache the result
     * @return mixed Cached or fresh result
     */
    public function remember(string $key, int $ttl, callable $callback) {
        // If Redis is disabled, execute callback directly
        if (!$this->enabled) {
            return $callback();
        }
        
        // Try to get from cache first
        if ($this->client->exists($key)) {
            $cached = $this->client->get($key);
            return json_decode($cached, true);
        }
        
        // Execute callback and cache result
        $result = $callback();
        $this->client->setex($key, $ttl, json_encode($result));
        return $result;
    }
    
    /**
     * Delete item from cache
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function forget(string $key): bool {
        if (!$this->enabled) {
            return false;
        }
        
        return (bool) $this->client->del($key);
    }
    
    /**
     * Clear all items from cache
     * 
     * @return bool Success status
     */
    public function flush(): bool {
        if (!$this->enabled) {
            return false;
        }
        
        return $this->client->flushDB();
    }
}
