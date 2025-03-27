<?php

namespace SGS\Cache\Engine;

use SGS\Cache\Engine\CacheEngineInterface;

class ApcuEngine implements CacheEngineInterface {
    protected $prefix;

    public function __construct(array $settings = []) {
        $this->prefix = $settings['prefix'] ?? 'cache_';
    }

    public function add(string $key, array $data, int $ttl = 0): void {
        $cacheKey = $this->prefix . $key;

        $existingData = apcu_fetch($cacheKey, $success);
        if ($success) {
            $existingData[] = $data;
            apcu_store($cacheKey, $existingData, $ttl);
        } else {
            apcu_store($cacheKey, [$data], $ttl);
        }
    }

    public function get(string $key): array {
        $cacheKey = $this->prefix . $key;
        $success = false;
        $cacheData = apcu_fetch($cacheKey, $success);
        return $success ? $cacheData : [];
    }

    public function flush(): void {
        apcu_clear_cache();
    }
}
