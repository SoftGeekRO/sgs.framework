<?php

namespace SGS\Utility;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use RuntimeException;

class FilePathResolver {

    protected static array $psr4Paths = [];

    /**
     * Get the absolute folder path of the composer.json file.
     *
     * @return string The absolute path to the directory containing composer.json.
     * @throws RuntimeException If composer.json cannot be located.
     */
    public static function getComposerJsonPath(): string {
        // Check if the COMPOSER_ROOT environment variable is set
        $composerRoot = getenv('COMPOSER_ROOT');
        if ($composerRoot && is_dir($composerRoot)) {
            return $composerRoot;
        }

        // Fallback: Traverse up the directory tree to find composer.json
        $currentDir = getcwd();
        while ($currentDir !== '/') {
            if (file_exists($currentDir . '/composer.json')) {
                return $currentDir;
            }
            $currentDir = dirname($currentDir);
        }

        throw new RuntimeException("composer.json file could not be found.");
    }

    /**
     * Get the Composer autoloader instance.
     *
     * @return ClassLoader The Composer autoloader.
     * @throws RuntimeException If the Composer autoloader is not found.
     */
    protected static function getComposerAutoloader(): ClassLoader {
        // Locate the Composer autoloader
        $autoloaderPath = self::getComposerJsonPath() . '/vendor/autoload.php';
        if (!file_exists($autoloaderPath)) {
            throw new RuntimeException("Composer autoloader not found at '{$autoloaderPath}'.");
        }

        // Load the Composer autoloader
        $autoloader = require $autoloaderPath;
        if (!$autoloader instanceof ClassLoader) {
            throw new RuntimeException("Composer autoloader is not an instance of ClassLoader.");
        }

        return $autoloader;
    }

    /**
     * Resolve a path relative to a PSR-4 namespace.
     *
     * @param string $namespace The namespace prefix.
     * @param string $relativePath The relative path.
     * @return string The resolved absolute path.
     * @throws InvalidArgumentException If the namespace or path is invalid.
     */
    public static function resolvePsr4Path(string $namespace, string $relativePath): string {
        // Normalize the namespace
        $namespace = trim($namespace, '\\') . '\\';

        // Check if the namespace exists in the PSR-4 paths
        if (!isset(self::$psr4Paths[$namespace])) {
            throw new InvalidArgumentException("Namespace '{$namespace}' is not defined in Composer's PSR-4 autoload.");
        }

        // Get the base directories for the namespace
        $baseDirs = self::$psr4Paths[$namespace];

        // Use the first base directory (assuming there's only one)
        $baseDir = rtrim($baseDirs[0], '/');

        // Resolve the relative path
        $resolvedPath = $baseDir . '/' . ltrim($relativePath, '/');
        if (!file_exists($resolvedPath)) {
            throw new InvalidArgumentException("The path '{$resolvedPath}' does not exist.");
        }

        return $resolvedPath;
    }

    public static function getSrcAbsolutePath(): string {
        $absPath = self::getComposerJsonPath() . '/src/';
        if (is_dir($absPath)) {
            return $absPath;
        }
        throw new InvalidArgumentException("The SRC path '{$absPath}' does not exist.");
    }

    /**
     * Resolve a path relative to the application base directory.
     *
     * @return string The resolved absolute path.
     * @throws InvalidArgumentException If the resolved path is invalid.
     */
    public static function getAppAbsolutePath(): string {
        $absPath = self::getComposerJsonPath() . '/app/';

        if (is_dir($absPath)) {
            return $absPath;
        }
        throw new InvalidArgumentException("The application path '{$absPath}' does not exist.");
    }

    /**
     * Resolve a path relative to the base directory.
     *
     * @param string $relativePath The relative path.
     * @return string The resolved absolute path.
     * @throws InvalidArgumentException If the resolved path is invalid.
     */
    public static function resolveRelativePath(string $relativePath): string {
        $resolvedPath = self::getComposerJsonPath() . '/' . ltrim($relativePath, '/');
        if (!file_exists($resolvedPath)) {
            throw new InvalidArgumentException("The path '{$resolvedPath}' does not exist.");
        }
        return $resolvedPath;
    }

    /**
     * Resolve a path relative to the current script's location.
     *
     * @param string $relativePath The relative path.
     * @return string The resolved absolute path.
     * @throws InvalidArgumentException If the resolved path is invalid.
     */
    public static function resolveFromCurrentLocation(string $relativePath): string {
        $currentDir = dirname(__DIR__); // Adjust based on your directory structure
        $resolvedPath = $currentDir . '/' . ltrim($relativePath, '/');
        if (!file_exists($resolvedPath)) {
            throw new InvalidArgumentException("The path '{$resolvedPath}' does not exist.");
        }
        return $resolvedPath;
    }

    /**
     * Get the path for log files.
     *
     * @param string $logFile The log file name.
     * @return string The resolved log file path.
     * @throws RuntimeException If the logs directory does not exist.
     */
    public static function getLogPath(string $logFile): string {
        $logDir = self::getComposerJsonPath() . '/app/Logs';
        if (!is_dir($logDir)) {
            throw new RuntimeException("The logs directory '{$logDir}' does not exist.");
        }
        return $logDir . '/' . ltrim($logFile, '/');
    }

    /**
     * Get the path for view files.
     *
     * @param string $viewFile The view file name.
     * @return string The resolved view file path.
     * @throws RuntimeException If the views directory does not exist.
     */
    public static function getViewPath(string $viewFile): string {
        $viewDir = self::getComposerJsonPath() . '/app/Views';
        if (!is_dir($viewDir)) {
            throw new RuntimeException("The views directory '{$viewDir}' does not exist.");
        }
        return $viewDir . '/' . ltrim($viewFile, '/');
    }

    /**
     * Get the path for asset files.
     *
     * @param string $assetFile The asset file name.
     * @return string The resolved asset file path.
     * @throws RuntimeException If the assets directory does not exist.
     */
    public static function getAssetPath(string $assetFile): string {
        $assetDir = self::getComposerJsonPath() . '/public/assets';
        if (!is_dir($assetDir)) {
            throw new RuntimeException("The assets directory '{$assetDir}' does not exist.");
        }
        return $assetDir . '/' . ltrim($assetFile, '/');
    }
}