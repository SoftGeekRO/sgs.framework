<?php

namespace SGS\Cache\Engine;

use SGS\Cache\Engine\CacheEngineInterface;

class MemcachedEngine implements CacheEngineInterface {
    protected $memcache;
    protected $prefix;

    public function __construct(array $settings = []) {
        $this->memcache = new \Memcache();
        $this->memcache->connect($settings['host'] ?? '127.0.0.1', $settings['port'] ?? 11211);
        $this->prefix = $settings['prefix'] ?? 'cache_';
    }

    public function add(string $key, array $data, int $ttl = 0): void {
        $cacheKey = $this->prefix . $key;
        $existingData = $this->memcache->get($cacheKey);

        if ($existingData) {
            $existingData[] = $data;
            $this->memcache->set($cacheKey, $existingData, 0, $ttl);
        } else {
            $this->memcache->set($cacheKey, [$data], 0, $ttl);
        }
    }

    public function get(string $key): array {
        $cacheKey = $this->prefix . $key;
        return $this->memcache->get($cacheKey) ?? [];
    }

    public function flush(): void {
        $this->memcache->flush();
    }
}