<?php

namespace SGS\Cache\Engine;

class FileEngine implements CacheEngineInterface {
    protected $path;

    public function __construct(array $settings = []) {
        $this->path = $settings['path'] ?? sys_get_temp_dir();
    }

    public function add(string $key, array $data, int $ttl = 0): void {
        $cacheFile = $this->getCacheFilePath($key);
        $existingData = $this->readCacheFile($cacheFile);
        $existingData[] = $data;

        file_put_contents($cacheFile, serialize($existingData));
    }

    public function get(string $key): array {
        $cacheFile = $this->getCacheFilePath($key);
        $data = $this->readCacheFile($cacheFile);

        // Filter expired logs
        $currentTime = time();
        return array_filter($data, function ($log) use ($currentTime) {
            return !isset($log['timestamp']) || ($currentTime - $log['timestamp']) <= $log['ttl'];
        });
    }

    public function flush(): void {
        array_map('unlink', glob($this->path . '/*.cache'));
    }

    protected function getCacheFilePath(string $key): string {
        return $this->path . DS . $key . '.cache';
    }

    protected function readCacheFile(string $cacheFile): array {
        if (file_exists($cacheFile)) {
            return unserialize(file_get_contents($cacheFile)) ?? [];
        }
        return [];
    }
}