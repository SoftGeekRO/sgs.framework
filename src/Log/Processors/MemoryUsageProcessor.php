<?php declare(strict_types=1);

namespace SGS\Log\Processors;

use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;

class MemoryUsageProcessor implements ProcessorInterface {
    private bool $realUsage;
    private bool $includePeak;

    public function __construct(bool $realUsage = true, bool $includePeak = true) {
        $this->realUsage = $realUsage;
        $this->includePeak = $includePeak;
    }

    public function __invoke(LogRecord $record): LogRecord {
        $record['extra']['memory'] = [
            'usage' => $this->formatBytes(memory_get_usage($this->realUsage)),
            'usage_bytes' => memory_get_usage($this->realUsage),
        ];

        if ($this->includePeak) {
            $record['extra']['memory']['peak'] = $this->formatBytes(memory_get_peak_usage($this->realUsage));
            $record['extra']['memory']['peak_bytes'] = memory_get_peak_usage($this->realUsage);
        }

        return $record;
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}