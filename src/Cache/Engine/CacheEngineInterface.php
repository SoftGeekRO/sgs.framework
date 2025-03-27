<?php

namespace SGS\Cache\Engine;

interface CacheEngineInterface {
    public function add(string $key, array $data, int $ttl = 0): void;
    public function get(string $key): array;
    public function flush(): void;
}