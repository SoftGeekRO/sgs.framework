<?php

namespace SGS\Cache\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class CacheAnnotation {
    public function __construct(
        public int $ttl = 0, // Time to live in seconds
        public string $key = '' // Custom cache key (optional)
    ) {}
}