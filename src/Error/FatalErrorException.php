<?php

namespace SGS\Error;

use SGS\Core\Exception\SGSException;
use Throwable;

/**
 * Represents a fatal error
 */
class FatalErrorException extends SGSException {

    /**
     * Constructor
     *
     * @param string $message Message string.
     * @param int|null $code Code.
     * @param string|null $file File name.
     * @param int|null $line Line number.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message, ?int $code = null, ?string $file = null, ?int $line = null, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        if ($file) {
            $this->file = $file;
        }
        if ($line) {
            $this->line = $line;
        }
    }
}