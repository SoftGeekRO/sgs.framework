<?php
declare(strict_types=1);

namespace SGS\Log\Formatters;

use Monolog\Formatter\{
    LineFormatter,
    JsonFormatter,
    SyslogFormatter
};

class FormatterFactory {
    public static function create(string $format): JsonFormatter|SyslogFormatter|LineFormatter {
        return match($format) {
            'json' => new JsonFormatter(),
            'syslog' => new SyslogFormatter(),
            'text' => new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message%\n".
                "Context: %context%\n".
                "Extra: %extra%\n\n",
                'Y-m-d H:i:s.v',
                true,
                true
            ),
            default => throw new \InvalidArgumentException("Invalid formatter type: $format"),
        };
    }
}