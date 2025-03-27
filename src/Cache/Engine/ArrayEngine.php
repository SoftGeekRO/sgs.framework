<?php

namespace SGS\Cache\Engine;

use SGS\Cache\CacheInterface;

class ArrayEngine implements CacheEngineInterface {
    protected static $cache = [];

    public function add(string $key, array $data, int $ttl = 0): void {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = [];
        }
        $data['ttl'] = $ttl;
        $data['timestamp'] = time(); // Store the current timestamp
        self::$cache[$key][] = $data;
    }

    public function get(string $key): array {
        if (!isset(self::$cache[$key])) {
            return [];
        }

        $currentTime = time();
        return array_filter(self::$cache[$key], function ($log) use ($currentTime) {
            return !isset($log['timestamp']) || ($currentTime - $log['timestamp']) <= $log['ttl'];
        });
    }

    public function flush(): void {
        self::$cache = [];
    }
}