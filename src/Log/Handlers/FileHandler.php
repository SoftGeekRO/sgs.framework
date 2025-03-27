<?php

namespace SGS\Log\Handlers;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\FormatterInterface;
use RuntimeException;
use SGS\Log\Formatters\FormatterFactory;

class FileHandler extends RotatingFileHandler {
    public function __construct(array $config) {
        $this->ensureFileExists($config['path']);

        parent::__construct(
            $config['path'],
            $config['days'] ?? 7,
            $config['level'],
            $config['bubble'] ?? true,
            $config['permission'] ?? 0664,
            $config['locking'] ?? false
        );

        if (isset($config['formatter'])) {
            $this->setFormatter(
                FormatterFactory::create(
                    $config['formatter'],
                    $config['formatter_config'] ?? []
                )
            );
        }
    }

    private function ensureFileExists(string $path): void {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException("Failed to create log directory: {$dir}");
            }
        }

        if (!file_exists($path)) {
            if (!touch($path)) {
                throw new RuntimeException("Failed to create log file: {$path}");
            }
            chmod($path, 0664);
        }
    }
}