<?php declare(strict_types=1);

namespace SGS\Log;

use Monolog\Level;
use SGS\Log\Handlers\HandlerFactory;
use SGS\Log\Processors\ProcessorFactory;
use RuntimeException;

class LogManager {
    private static array $channels = [];
    private static array $config = [];
    private static bool $initialized = false;

    public static function init(array $config): void {
        if (self::$initialized) {
            throw new RuntimeException('Logger already initialized');
        }

        self::$config = $config;
        self::$initialized = true;
        self::ensureStorageExists();
    }

    public static function channel(string $name = null): Logger {
        $channel = $name ?? self::$config['default']['channel'] ?? 'app';

        if (!isset(self::$channels[$channel])) {
            self::$channels[$channel] = self::createChannel($channel);
        }

        return self::$channels[$channel];
    }

    private static function createChannel(string $name): Logger {
        $config = self::$config['channels'][$name] ?? [];
        $logger = new Logger($name);

        // Add handlers
        foreach ($config['handlers'] ?? [] as $handlerConfig) {
            $logger->addHandler(HandlerFactory::create($handlerConfig));
        }

        // Add processors
        foreach (ProcessorFactory::createFromConfig(self::$config['processors'] ?? []) as $processor) {
            $logger->addProcessor($processor);
        }

        return $logger;
    }

    private static function ensureStorageExists(): void {
        $paths = [
            self::$config['storage']['logs'] ?? LOGS,
            self::$config['storage']['archives'] ?? LOGS. DS . 'archives'
        ];

        foreach ($paths as $path) {
            if (!file_exists($path) && !mkdir($path, 0755, true)) {
                throw new RuntimeException("Failed to create directory: {$path}");
            }
        }
    }

    public static function __callStatic(string $method, array $args) {
        return self::channel()->{$method}(...$args);
    }
}