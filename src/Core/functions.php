<?php

use SGS\Config\Config;

if (!function_exists('SGS\Core\pr')) {
    /**
     * print_r() convenience function.
     *
     * In terminals this will act similar to using print_r() directly, when not run on CLI
     * print_r() will also wrap `<pre>` tags around the output of given variable. Similar to debug().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     */
    function pr(mixed $var): mixed {
        if (!Config::get('debug')) {
            return $var;
        }

        $template = PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ? '<pre class="pr">%s</pre>' : "\n%s\n\n";
        printf($template, trim(print_r($var, true)));

        return $var;
    }
}

if (!function_exists('SGS\Core\pj')) {
    /**
     * JSON pretty print convenience function.
     *
     * In terminals this will act similar to using json_encode() with JSON_PRETTY_PRINT directly, when not run on CLI
     * will also wrap `<pre>` tags around the output of given variable. Similar to pr().
     *
     * This function returns the same variable that was passed.
     *
     * @param mixed $var Variable to print out.
     * @return mixed the same $var that was passed to this function
     * @see pr()
     */
    function pj(mixed $var): mixed {
        if (!Config::get('debug')) {
            return $var;
        }

        $template = PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ? '<pre class="pj">%s</pre>' : "\n%s\n\n";
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        printf($template, trim((string)json_encode($var, $flags)));

        return $var;
    }
}

if (!function_exists('SGS\Core\env')) {
    /**
     * Gets an environment variable from available sources, and provides emulation
     * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
     * IIS, or SCRIPT_NAME in CGI mode). Also exposes some additional custom
     * environment information.
     *
     * @param string $key Environment variable name.
     * @param string|bool|null $default Specify a default value in case the environment variable is not defined.
     * @return string|float|int|bool|null Environment variable setting.
     */
    function env(string $key, string|float|int|bool|null $default = null): string|float|int|bool|null {
        if ($key === 'HTTPS') {
            if (isset($_SERVER['HTTPS'])) {
                return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            }

            return str_starts_with((string)env('SCRIPT_URI'), 'https://');
        }

        if ($key === 'SCRIPT_NAME' && env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
            $key = 'SCRIPT_URL';
        }

        $val = $_SERVER[$key] ?? $_ENV[$key] ?? null;
        assert($val === null || is_scalar($val));
        if ($val == null && getenv($key) !== false) {
            $val = (string)getenv($key);
        }

        if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
            $addr = env('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null) {
                $val = $addr;
            }
        }

        if ($val !== null) {
            return $val;
        }

        switch ($key) {
            case 'DOCUMENT_ROOT':
                $name = (string)env('SCRIPT_NAME');
                $filename = (string)env('SCRIPT_FILENAME');
                $offset = 0;
                if (!str_ends_with($name, '.php')) {
                    $offset = 4;
                }

                return substr($filename, 0, -(strlen($name) + $offset));
            case 'PHP_SELF':
                return str_replace((string)env('DOCUMENT_ROOT'), '', (string)env('SCRIPT_FILENAME'));
            case 'CGI_MODE':
                return PHP_SAPI === 'cgi';
        }

        return $default;
    }
}