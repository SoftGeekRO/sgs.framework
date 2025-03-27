<?php
namespace SGS\Config;

use Dotenv\Dotenv;
use RuntimeException;
use SGS\Utility\FilePathResolver;

class Config {
    private static array $config = [];
    protected static $fileTimestamps = [];
    protected static array $requiredSettings = [
        'debug',
        'logs',
    ];

    /**
     * Validate that all required settings are present.
     *
     * @throws RuntimeException If a required setting is missing.
     */
    protected static function validateRequiredSettings(): void {
        foreach (self::$requiredSettings as $setting) {
            if (!isset(self::$config[$setting])) {
                throw new RuntimeException("Required setting '$setting' is missing.");
            }
        }
    }

    /**
     * Get the path for config files.
     *
     * @param string $configFile The config file name.
     * @return string The resolved config file path.
     * @throws RuntimeException If the config directory does not exist.
     */
    public static function getConfigPath(string $configFile = '/config'): string {
        $configDir = FilePathResolver::getAppAbsolutePath() . ltrim($configFile, '/');
        if (!is_dir($configDir)) {
            throw new RuntimeException("The application config directory '{$configDir}' does not exist.");
        }
        var_dump($configDir);
        return $configDir;
    }

    public static function getDefaultConfigFile(string $configFilePath = 'Config/defaults.php'): string {
        $configDir = SRC . DS . ltrim($configFilePath, '/');
        if (!file_exists($configDir)) {
            throw new RuntimeException("The default config file '{$configDir}' does not exist.");
        }
        return $configDir;

    }

    public static function getEnvFilePath(): string {
        $envFile = FilePathResolver::getComposerJsonPath() . "/.env";

        if (!file_exists($envFile)) {
            throw new RuntimeException("The .env file '{$envFile}' does not exist.");
        }
        return $envFile;
    }

    /**
     * Load all configuration files from the config directory.
     */
    public static function load(?string $configAppDir = null, ?string $configEnvFile = null): void {

        // Load default configurations (lowest priority)
        $cfgDefaultFile = self::getDefaultConfigFile();
        self::$config = require $cfgDefaultFile;
        self::$fileTimestamps[$cfgDefaultFile] = filemtime($cfgDefaultFile);

        // Load application configurations (higher priority)
        $appConfigsPath = $configAppDir ?? self::getConfigPath();
        foreach (glob("$appConfigsPath*.php") as $configFile) {
            $key = basename($configFile, '.php');
            $configs = require $configFile;

            self::$config = self::combine_config(self::$config, $configs);
            self::$fileTimestamps[$configFile] = filemtime($configFile);
        }


        $envFile = $configEnvFile ?? self::getEnvFilePath();
        // Load environment variables (highest priority)
        if (file_exists($envFile)) {
            $dotenv = Dotenv::createImmutable(dirname($envFile)); // Path to .env directory
            $dotenv->load();

            // Process environment variables to parse arrays
            $processedEnv = array_map(function ($value) {
                return self::parseEnvValue($value);
            }, $_ENV);

            // Merge processed environment variables
            self::$config = self::combine_config(self::$config, $processedEnv);
            self::$fileTimestamps[$envFile] = filemtime($envFile);
        }

        self::validateRequiredSettings();
    }

    protected static function reloadIfNeeded(): void {
        foreach (self::$fileTimestamps as $file => $timestamp) {
            if (filemtime($file) > $timestamp) {
                self::load(); // Reload configurations if any file is modified
                break;
            }
        }
    }

    /**
     * Parse an environment variable value to its appropriate type.
     *
     * @param mixed $value The raw environment variable value.
     * @return mixed The parsed value.
     */
    protected static function parseEnvValue(mixed $value): mixed {
        if (is_string($value)) {
            // Handle "true"/"false" as booleans
            if ($value === 'true') return true;
            if ($value === 'false') return false;

            // Handle numeric values
            if (is_numeric($value)) return (float)$value;

            // Split into arrays if comma-separated
            if (str_contains($value, ',')) {
                return array_map('trim', explode(',', $value));
            }
        }

        // Return the value as-is if no parsing is needed
        return $value;
    }

    /**
     * Get a configuration value by dot notation (e.g., 'app.debug').
     *
     * @param string $key The configuration key in dot notation.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed The configuration value or the default value.
     */
    public static function get(string $key, mixed $default = null): mixed {

        self::reloadIfNeeded();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a configuration value by dot notation (e.g., 'app.debug').
     *
     * @param string $key The configuration key in dot notation.
     * @param mixed $value The value to set.
     */
    public static function set(string $key, mixed $value): void {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    private static function combine_config(array &$array1, array &$array2) {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::combine_config($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Get all configuration values.
     *
     * @return array
     */
    public static function all() {
        return self::$config;
    }
}
