<?php

namespace SGS\Utils;

use ReflectionClass;
use ReflectionMethod;
use SGS\Config\Config;

class ClassResolver {
    protected static $namespaces = [];

    /**
     * Initialize the ClassResolver with namespaces from the configuration.
     */
    public static function initialize(): void {
        // Load the application namespace from the config
        $appNamespace = Config::get('app.namespace', 'App\\');

        // Load the default framework namespace from the config
        $frameworkNamespace = Config::get('framework.namespace', 'SGS\\Core\\');

        // Initialize namespaces
        self::$namespaces = [$frameworkNamespace, $appNamespace];
    }

    /**
     * Resolve a class name using the registered namespaces.
     *
     * @param string $className The class name to resolve.
     * @return string The fully qualified class name.
     * @throws \RuntimeException If the class cannot be resolved.
     */
    public static function resolve(string $className): string {
        foreach (self::$namespaces as $namespace) {
            $fullyQualifiedClassName = rtrim($namespace, '\\') . '\\' . ltrim($className, '\\');

            if (class_exists($fullyQualifiedClassName)) {
                return $fullyQualifiedClassName;
            }
        }

        throw new \RuntimeException("Class not found: $className");
    }

    /**
     * Add a namespace to the resolver.
     *
     * @param string $namespace The namespace to add.
     */
    public static function addNamespace(string $namespace): void {
        self::$namespaces[] = rtrim($namespace, '\\');
    }

    /**
     * Get all methods of a class.
     *
     * @param string $className The class name.
     * @return array An array of method names.
     */
    public static function getMethods(string $className): array {
        $fullyQualifiedClassName = self::resolve($className);
        $reflection = new ReflectionClass($fullyQualifiedClassName);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        return array_map(function ($method) {
            return $method->getName();
        }, $methods);
    }

    /**
     * Get the short name of a class (without namespace).
     *
     * @param string $className The class name.
     * @return string The short class name.
     */
    public static function getShortClassName(string $className): string {
        $fullyQualifiedClassName = self::resolve($className);
        return (new ReflectionClass($fullyQualifiedClassName))->getShortName();
    }

    /**
     * Get the fully qualified class name.
     *
     * @param string $className The class name.
     * @return string The fully qualified class name.
     */
    public static function getFullyQualifiedClassName(string $className): string {
        return self::resolve($className);
    }

    /**
     * Get the file path of a class.
     *
     * @param string $className The class name.
     * @return string The file path of the class.
     */
    public static function getClassFilePath(string $className): string {
        $fullyQualifiedClassName = self::resolve($className);
        return (new ReflectionClass($fullyQualifiedClassName))->getFileName();
    }

    /**
     * Check if a class exists.
     *
     * @param string $className The class name.
     * @return bool True if the class exists, false otherwise.
     */
    public static function classExists(string $className): bool {
        try {
            self::resolve($className);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Get the namespace of a class.
     *
     * @param string $className The class name.
     * @return string The namespace of the class.
     */
    public static function getNamespace(string $className): string {
        $fullyQualifiedClassName = self::resolve($className);
        return (new ReflectionClass($fullyQualifiedClassName))->getNamespaceName();
    }
}