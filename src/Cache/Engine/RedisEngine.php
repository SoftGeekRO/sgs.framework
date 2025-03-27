<?php

namespace SGS\Cache\Engine;

use Redis;
use SGS\Cache\CacheInterface;

class RedisEngine implements CacheEngineInterface {
    protected $redis;
    protected $prefix;

    public function __construct(array $settings = []) {
        $this->redis = new \Redis();
        $this->redis->connect($settings['host'] ?? '127.0.0.1', $settings['port'] ?? 6379);
        $this->prefix = $settings['prefix'] ?? 'cache_';
    }

    public function add(string $key, array $data, int $ttl = 0): void {
        $cacheKey = $this->prefix . $key;
        $this->redis->rPush($cacheKey, serialize($data));

        if ($ttl > 0) {
            $this->redis->expire($cacheKey, $ttl);
        }
    }

    public function get(string $key): array {
        $cacheKey = $this->prefix . $key;
        $data = $this->redis->lRange($cacheKey, 0, -1);
        return array_map('unserialize', $data);
    }

    public function flush(): void {
        $this->redis->flushAll();
    }
}