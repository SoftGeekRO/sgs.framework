<?php

namespace SGS\Log\Handlers;

use Monolog\Handler\StreamHandler as MonologStreamHandler;
use Monolog\Formatter\FormatterInterface;
use SGS\Log\Formatters\FormatterFactory;
use RuntimeException;

class StreamHandler extends MonologStreamHandler {
    public function __construct(array $config) {
        $stream = $this->resolveStream($config['stream']);
        $level = $config['level'];
        $bubble = $config['bubble'] ?? true;
        $filePermission = $config['permission'] ?? null;
        $useLocking = $config['locking'] ?? false;

        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);

        if (isset($config['formatter'])) {
            $this->setFormatter(
                FormatterFactory::create(
                    $config['formatter'],
                    $config['formatter_config'] ?? []
                )
            );
        }
    }

    private function resolveStream($stream) {
        if (is_resource($stream)) {
            return $stream;
        }

        if (null === $stream || '' === $stream) {
            $stream = 'php://stderr';
        }

        if ('php://stdout' === $stream || 'php://stderr' === $stream) {
            return $stream;
        }

        if (!file_exists($stream) && !touch($stream)) {
            throw new RuntimeException("Cannot create log file at {$stream}");
        }

        return $stream;
    }
}