<?php

namespace SGS\Cache;

use ReflectionClass;
use RuntimeException;
use SGS\Cache\Engine\ArrayEngine;
use SGS\Cache\Engine\FileEngine;
use SGS\Cache\Engine\RedisEngine;
use SGS\Config\Config;

class Cache {

    private static ?Cache $instance = null;

    public static CacheInterface $cacheInstance;

    /**
     * Create a cache instance based on the configuration.
     *
     * @param string|null $configCacheDir
     * @return CacheInterface
     */
    public static function initialize(?string $configCacheDir = null): CacheInterface {
        $driver = Config::get('cache.driver', 'file');

        switch ($driver) {
            case 'file':
                $cacheDir = $configCacheDir ?? Config::get('cache.file.path', 'app/cache');
                self::$cacheInstance = new FileEngine($cacheDir);
                return self::$cacheInstance;
            case 'memory':
                self::$cacheInstance = new ArrayEngine();
                return self::$cacheInstance;
            case 'redis':
                $redisConfig = [
                    'host' => Config::get('cache.redis.host', '127.0.0.1'),
                    'port' => Config::get('cache.redis.port', 6379),
                    'password' => Config::get('cache.redis.password', ''),
                ];
                self::$cacheInstance = new RedisEngine($redisConfig);
                return self::$cacheInstance;
            default:
                throw new RuntimeException("Unsupported cache driver: $driver");
        }
    }

    public static function getInstance(): ?Cache {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * CacheAnnotation the result of a method or class using annotations.
     *
     * @param object $instance The class instance.
     * @param string $method The method name.
     * @param array $args The method arguments.
     * @return mixed The cached result or the result of the method.
     */
    public static function handleCache(object $instance, string $method, array $args = []) {
        $reflection = new ReflectionClass($instance);
        $methodReflection = $reflection->getMethod($method);

        // Check for @CacheAnnotation annotation on the method
        $methodCacheAttributes = $methodReflection->getAttributes(Cache::class);
        if (!empty($methodCacheAttributes)) {
            $cacheConfig = $methodCacheAttributes[0]->newInstance();
            return self::decorateMethod($instance, $method, $args, $cacheConfig);
        }

        // Check for @CacheAnnotation annotation on the class
        $classCacheAttributes = $reflection->getAttributes(Cache::class);
        if (!empty($classCacheAttributes)) {
            $cacheConfig = $classCacheAttributes[0]->newInstance();
            return self::decorateMethod($instance, $method, $args, $cacheConfig);
        }

        // No caching, call the method directly
        return call_user_func_array([$instance, $method], $args);
    }

    /**
     * Decorate a method with caching.
     */
    protected static function decorateMethod(object $instance, string $method, array $args, Cache $cacheConfig) {
        $cache = self::create();

        // Generate a cache key if not provided
        $key = $cacheConfig->key ?: self::generateCacheKey($instance, $method, $args);

        if ($cache->has($key)) {
            return $cache->get($key);
        }

        $result = call_user_func_array([$instance, $method], $args);
        $cache->set($key, $result, $cacheConfig->ttl);

        return $result;
    }

    /**
     * Generate a cache key based on the class, method, and arguments.
     */
    protected static function generateCacheKey(object $instance, string $method, array $args): string {
        $class = get_class($instance);
        $argsHash = md5(serialize($args));
        return "cache_{$class}_{$method}_{$argsHash}";
    }
}