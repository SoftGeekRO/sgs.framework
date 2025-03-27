<?php

namespace SGS\Log\Handlers;

use Monolog\Handler\SyslogHandler as MonologSyslogHandler;
use Monolog\Formatter\FormatterInterface;
use SGS\Log\Formatters\FormatterFactory;

class SyslogHandler extends MonologSyslogHandler {
    public function __construct(array $config) {
        parent::__construct(
            $config['ident'],
            $config['facility'] ?? LOG_USER,
            $this->convertLevel($config['level']),
            $config['bubble'] ?? true,
            $config['logopts'] ?? LOG_PID
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

    private function convertLevel(string $level): int {
        return match(strtolower($level)) {
            'debug' => LOG_DEBUG,
            'notice' => LOG_NOTICE,
            'warning' => LOG_WARNING,
            'error' => LOG_ERR,
            'critical' => LOG_CRIT,
            'alert' => LOG_ALERT,
            'emergency' => LOG_EMERG,
            default => LOG_INFO,
        };
    }
}