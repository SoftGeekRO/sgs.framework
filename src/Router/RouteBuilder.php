<?php

namespace SGS\Router;

use SGS\Config\Config;

class RouteBuilder {
    private static array $routes = [];
    private static ?string $controllerNamespace = null;

    public static function initialize(): void {
        self::$routes = Config::get("routes", []);
        self::$controllerNamespace = Config::get('App.namespace') .'\\Controllers';
    }

    public static function get(string $path, array $controllerAction): Route {
        return self::buildRoute('GET', $path, $controllerAction);
    }

    public static function post(string $path, array $controllerAction): Route {
        return self::buildRoute('POST', $path, $controllerAction);
    }

    public static function put(string $path, array $controllerAction): Route {
        return self::buildRoute('PUT', $path, $controllerAction);
    }

    public static function delete(string $path, array $controllerAction): Route {
        return self::buildRoute('DELETE', $path, $controllerAction);
    }

    private static function resolveControllerClass(string $className): string {
        $fqcn = self::$controllerNamespace . '\\' . $className;
        if (!class_exists($fqcn)) {
            throw new \InvalidArgumentException(
                "Controller class {$fqcn} does not exist"
            );
        }

        return $fqcn;
    }

    private static function buildRoute(string $method, string $path, array $controllerAction): Route {
        // Ensure RouteBuilder is initialized
        if (self::$controllerNamespace === null) {
            throw new \RuntimeException('RouteBuilder must be initialized before adding routes.');
        }

        $controller = self::resolveControllerClass($controllerAction[0]);
        $action = $controllerAction[1];

        $route = new Route(
            $method,
            $path,
            $controller, // Controller class
            $action,  // Controller method,
        );

        self::$routes[] = $route;
        return $route;
    }

    public static function getRoutes(): array {
        return self::$routes;
    }
}

