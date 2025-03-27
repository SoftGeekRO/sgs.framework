<?php

if (!function_exists('collect_errors')) {
    /**
     * Collect errors or exceptions.
     *
     * @param $error
     * @return array
     */
    function collect_errors($error): array {
        static $errors = [];

        if ($error) {
            $errors[] = [
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTrace(),
                'traceString' => $error->getTraceAsString(),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        }

        return $errors;
    }
}

if (!function_exists('get_collected_errors')) {
    /**
     * Get collected errors or exceptions.
     *
     * @return array
     */
    function get_collected_errors(): array {
        return collect_errors(null);
    }
}