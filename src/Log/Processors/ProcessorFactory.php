<?php declare(strict_types=1);

namespace SGS\Log\Processors;

use Monolog\Processor\ProcessorInterface;
use RuntimeException;

class ProcessorFactory {
    public static function createFromConfig(array $config): array {
        $processors = [];

        foreach ($config as $processorConfig) {
            $processors[] = self::create($processorConfig);
        }

        return $processors;
    }

    public static function create($config): ProcessorInterface {
        $className = is_array($config) ? $config['class'] : $config;
        $args = is_array($config) ? $config['args'] ?? [] : [];

        if (!class_exists($className)) {
            throw new RuntimeException("Processor class {$className} not found");
        }

        // Special case for Monolog's built-in processors
        if ($className === MemoryUsageProcessor::class) {
            return new $className(
                $args['realUsage'] ?? true,
                $args['useFormatting'] ?? true
            );
        }

        // Special handling for our EnvironmentProcessor
        if ($className === EnvironmentProcessor::class) {
            return new $className(
                $args['envVars'] ?? [],
                $args['includeServer'] ?? false
            );
        }

        try {
            return empty($args) ? new $className() : new $className(...$args);
        } catch (\Throwable $e) {
            throw new RuntimeException("Failed to create processor: {$e->getMessage()}");
        }
    }
}