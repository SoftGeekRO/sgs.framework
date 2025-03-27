<?php
declare(strict_types=1);

namespace SGS\Log\Processors;

use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;
use Random\RandomException;

class UidProcessor implements ProcessorInterface {
    private string $uid;
    private int $length;

    public function __construct(int $length = 7) {
        $this->length = $length;
        $this->uid = $this->generateUid();
    }

    public function __invoke(LogRecord $record): LogRecord {
        $record['extra']['uid'] = $this->uid;
        return $record;
    }

    /**
     * @throws RandomException
     */
    private function generateUid(): string {
        return substr(bin2hex(random_bytes((int)ceil($this->length / 2))), 0, $this->length);
    }
}