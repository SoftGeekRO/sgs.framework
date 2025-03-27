<?php

namespace SGS\Error;

use Monolog\Level;
use Throwable;

//use SGS\Log\ErrorLogger;
use SGS\Signal\Signal;
use SGS\View\AppView;

class ExceptionTrap extends ErrorBase {

    protected array $_config = [];

    protected ?Signal $signal = null;

    //private ErrorLogger $errorLogger;

    protected string $exceptionTemplate = '/error/exception.html.twig';

    /**
     * Track if this trap was removed from the global handler.
     *
     * @var bool
     */
    protected bool $disabled = false;

    public function __construct(array $options = []) {
        $this->_config = $options;
        $this->signal = Signal::getInstance();
        //$this->errorLogger = ErrorLogger::getInstance();
    }

    public function register(): void {
        set_exception_handler($this->handleException(...));
        register_shutdown_function($this->handleShutdown(...));

        ini_set('assert.exception', '1');
    }

    public function handleException(Throwable $exception): void {
        logger()->error(
            sprintf(
                'Uncaught Exception %s: "%s" at %s line %s',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ), ['context' => $exception->getTrace()]
        );
        collect_errors($exception); // Collect the exception
    }

    /**
     * Shutdown handler
     *
     * Convert fatal errors into exceptions that we can render.
     *
     * @return void
     * @throws \Exception
     */
    public function handleShutdown(): void {

        if ($this->disabled) {
            return;
        }

        $megabytes = $this->_config['extraFatalErrorMemory'] ?? 4;
        if ($megabytes > 0) {
            $this->increaseMemoryLimit($megabytes * 1024);
        }

        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            logger()->error(
                sprintf(
                    'Fatal Error: %s in %s on line %d',
                    $error['message'],
                    $error['file'],
                    $error['line']
                ),
                [
                    'type' => $error['type'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]
            );
            collect_errors(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }

        // Render the error page if there are collected errors
        $errors = get_collected_errors();
        try {
            if (!empty($errors)) {
                $view = new AppView();
                echo $view->renderError($this->exceptionTemplate, ['errors' => $errors]);
                http_response_code(500); // Set a generic error status code
            }
        } catch (Throwable $exception) {
            $this->logInternalError($exception);
        }
    }

    /**
     * Increases the PHP "memory_limit" ini setting by the specified amount
     * in kilobytes
     *
     * @param int $additionalKb Number in kilobytes
     * @return void
     */
    public function increaseMemoryLimit(int $additionalKb): void {
        $limit = ini_get('memory_limit');
        if ($limit === false || $limit === '' || $limit === '-1') {
            return;
        }
        $limit = trim($limit);
        $units = strtoupper(substr($limit, -1));
        $current = (int)substr($limit, 0, -1);
        if ($units === 'M') {
            $current *= 1024;
            $units = 'K';
        }
        if ($units === 'G') {
            $current = $current * 1024 * 1024;
            $units = 'K';
        }

        if ($units === 'K') {
            ini_set('memory_limit', ceil($current + $additionalKb) . 'K');
        }
    }

    /**
     * Trigger an error that occurred during rendering an exception.
     *
     * By triggering an E_USER_WARNING we can end up in the default
     * exception handling which will log the rendering failure,
     * and hopefully render an error page.
     *
     * @param Throwable $exception Exception to log
     * @return void
     */
    public function logInternalError(Throwable $exception): void {
        $message = sprintf(
            '[%s] %s (%s:%s)', // Keeping same message format
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        );
        trigger_error($message, E_USER_WARNING);
    }


}
