<?php

namespace SGS\Log\Processors;

use Monolog\Processor\WebProcessor as WEBP;
use Monolog\Processor\ProcessorInterface;

class WebProcessor implements ProcessorInterface {
    private WEBP $webProcessor;

    public function __construct(array $serverData = null, array $extraFields = null)  {
        $this->webProcessor = new WEBP($serverData, $extraFields);
    }

    public function __invoke($record) {
        $record = $this->webProcessor->__invoke($record);

        // Add additional web context
        $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $record['extra']['method'] = $_SERVER['REQUEST_METHOD'] ?? 'cli';
        $record['extra']['uri'] = $_SERVER['REQUEST_URI'] ?? 'cli';

        return $record;
    }
}