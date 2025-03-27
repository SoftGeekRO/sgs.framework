<?php

namespace SGS\Core;

use SGS\Config\Config;
use SGS\Middleware\ErrorHandlerMiddleware;
use SGS\Router\Router;

class Application {
    private Router $router;

    public function __construct() {

        $this->router = new Router();
        $this->boot();

    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void {
        //$this->registerMiddleware();
        $this->registerRoutes();
    }

    /**
     * Register middleware.
     */
    private function registerMiddleware(): void {
        foreach (Config::get('middleware') as $middlewareClass) {
            if ($middlewareClass === ErrorHandlerMiddleware::class) {
                $middleware = new $middlewareClass(Config::get('app.debug'), $this->router->getLogger());
            } else {
                $middleware = new $middlewareClass();
            }
            $this->router->addMiddleware([$middleware, 'handle']);
        }
    }

    /**
     * Register routes.
     */
    private function registerRoutes(): void {
        foreach (Config::get('routes') as $path => $route) {
            $this->router->addRoute($route->getMethod(), $route->getPath(), $route->getController(), $route->getAction());
        }
    }

    public function run(): void {

        // Dispatch request
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        $this->router->dispatch($uri, $httpMethod);
    }
}