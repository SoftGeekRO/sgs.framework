<?php
declare(strict_types=1);

namespace SGS\Error;

use ErrorException;
use SGS\Config\Config;

//use SGS\Log\ErrorLogger;
use SGS\Log\Logger;

class ErrorTrap extends ErrorBase {

    protected array $_config = [];

    //private ErrorLogger $errorLogger;

    protected string $errorTemplate = '/error/error.html.twig';

    public function __construct(array $options = []) {
        $this->_config = $options;
        //$this->errorLogger = ErrorLogger::getInstance();
    }

    public function register(): void {
        $level = $this->_config['errorLevel'] ?? -1;

        error_reporting($level);
        set_error_handler($this->handleError(...), $level);
    }

    /**
     * Handle PHP errors (convert them to exceptions).
     */
    public function handleError(int $level, string $description, string $file = null, int $line = 0, $errcontext = []): bool {
        if (!(error_reporting() & $level)) {
            return false;
        }

        if ($level === E_USER_ERROR || $level === E_ERROR || $level === E_PARSE) {
            throw new FatalErrorException($description, $level, $file, $line);
        }

        $error = new PhpError($level, $description, $file, $line);

        $context = [
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'context' => $errcontext,
        ];
        logger()->error($error->getMessage(), $context);

        collect_errors(
            new \ErrorException(
                $error->getMessage(),
                $error->getCode(),
                $error->getLogLevel(),
                $error->getFile(),
                $error->getLine())
        ); // Collect the exception
        return true;
    }

}