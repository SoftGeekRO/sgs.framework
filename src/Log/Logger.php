<?php

declare(strict_types=1);

namespace SGS\Log;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\ProcessorInterface;

class Logger extends MonologLogger {
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
    }

    public function addHandler(HandlerInterface $handler): self {
        $this->pushHandler($handler);
        return $this;
    }

    public function addProcessor(ProcessorInterface $processor): self {
        $this->pushProcessor($processor);
        return $this;
    }
}