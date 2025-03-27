<?php declare(strict_types=1);

namespace SGS\Log\Handlers;

use InvalidArgumentException;
use Monolog\Handler\HandlerInterface;

use SGS\Log\Handlers\RotatingFileHandler;


class HandlerFactory {

    /**
     * @param array $config
     * @return HandlerInterface
     */
    public static function create(array $config): HandlerInterface {
        if (!isset($config['type'])) {
            throw new InvalidArgumentException('Handler type must be specified');
        }

        $handlerClass = match($config['type']) {
            'file' => FileHandler::class,
            'rotating' => RotatingFileHandler::class,
            'stream' => StreamHandler::class,
            'syslog' => SyslogHandler::class,
            default => throw new InvalidArgumentException(
                "Invalid handler type: {$config['type']}"
            ),
        };

        return new $handlerClass($config);
    }
}