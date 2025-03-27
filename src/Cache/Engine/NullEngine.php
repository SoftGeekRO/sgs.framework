<?php

namespace SGS\Cache\Engine;

use SGS\Cache\Engine\CacheEngineInterface;

class NullEngine implements CacheEngineInterface {
    public function add(string $key, array $data, int $ttl = 0): void {
        // Do nothing
    }

    public function get(string $key): array {
        return [];
    }

    public function flush(): void {
        // Do nothing
    }
}