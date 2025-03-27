<?php declare(strict_types=1);

namespace SGS\Log\Handlers;

use Monolog\Handler\RotatingFileHandler as MonologRotatingHandler;
use Monolog\Formatter\FormatterInterface;
use SGS\Log\Formatters\FormatterFactory;
use RuntimeException;

class RotatingFileHandler extends MonologRotatingHandler {
    public function __construct(array $config) {
        $filename = $config['path'];
        $maxFiles = $config['max_files'] ?? 7;
        $level = $config['level'];
        $bubble = $config['bubble'] ?? true;
        $filePermission = $config['file_permission'] ?? 0644;
        $useLocking = $config['use_locking'] ?? false;

        $this->ensureFileExists($filename);

        parent::__construct(
            $filename,
            $maxFiles,
            $level,
            $bubble,
            $filePermission,
            $useLocking
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
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new RuntimeException("Failed to create directory: {$dir}");
        }

        if (!file_exists($path) && !touch($path)) {
            throw new RuntimeException("Failed to create log file: {$path}");
        }
    }
}