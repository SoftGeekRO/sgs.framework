<?php

namespace SGS\Cache;

interface CacheInterface {
    /**
     * Get a cached item by key.
     *
     * @param string $key The cache key.
     * @return mixed|null The cached value or null if not found.
     */
    public function get(string $key);

    /**
     * Store an item in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to cache.
     * @param int $ttl Time to live in seconds.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, $value, int $ttl = 0): bool;

    /**
     * Delete a cached item by key.
     *
     * @param string $key The cache key.
     * @return bool True on success, false on failure.
     */
    public function delete(string $key): bool;

    /**
     * Clear the entire cache.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;

    /**
     * Check if a cached item exists.
     *
     * @param string $key The cache key.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(string $key): bool;
}