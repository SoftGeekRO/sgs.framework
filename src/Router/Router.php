<?php
namespace SGS\Router;

use ReflectionClass;
use SGS\Cache\Annotations\CacheAnnotation as CacheAnnotation;
use SGS\Cache\Cache;
use SGS\Cache\CacheInterface;
use SGS\Signal\Signal;

//use SGS\Middleware\ErrorMiddleware;

class Router {
    private array $routes = [];
    private array $middleware = [];

    protected CacheInterface $cache;

    protected Signal $signal;

    function __construct($routes = []) {
        //$this->cache = Cache::getInstance()::$cacheInstance;
        $this->signal = Signal::getInstance();
    }

    /**
     * Add a route to the router.
     */
    public function addRoute(string $httpMethod, string $path, string $controller, string $action, array $middleware = []): void {
        $this->routes[$httpMethod][$path] = [
            'controller' => $controller,
            'method' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Add middleware to the router.
     */
    public function addMiddleware(callable $middleware): void {
        $this->middleware[] = $middleware;
    }

    /**
     * Dispatch the request to the appropriate controller and method.
     */
    public function dispatch(string $path, string $httpMethod): mixed {

        // Extract $_GET arguments
        $urlPath = parse_url($path, PHP_URL_PATH) ?? '';

        // Check if the requested path is for a static asset (CSS, JS, images, etc.)
        // @TODO: make something better on this part. Not always is necessary to process assets file throw php
        $filePath = realpath(WEBROOT . $urlPath);
        if ($filePath && is_file($filePath) && str_starts_with($filePath, WEBROOT)) {
            return $this->serveStaticFile($filePath);
        }

        if ($httpMethod === 'HEAD') {
            return [];
        }

        try {
            // Run global middleware before handling the request
            $this->runMiddleware();

            if (array_key_exists($urlPath, $this->routes[$httpMethod])) {
                $route = $this->routes[$httpMethod][$urlPath] ?? null;

                // Run route-specific middleware
                $this->runMiddleware($route['middleware']);

                $controller = $route['controller'];
                $method = $route['method'];

                if (!class_exists($controller)) {
                    throw new \Exception("Controller not found: $controller", 404);
                }

                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method not found: $method in $controller", 404);
                }

                $controllerInstance = new $controller();

                // Check for @CacheAnnotation annotation on the method
                $reflection = new ReflectionClass($controllerInstance);
                $methodReflection = $reflection->getMethod($method);
                $parameters = $methodReflection->getParameters();

                // Map URL arguments to method parameters
                $methodArgs = [];
                foreach ($parameters as $parameter) {
                    $methodArgs[] = $urlArgs[$parameter->getPosition()] ?? null;
                }

                $queryString = parse_url($path, PHP_URL_QUERY) ?? '';
                parse_str($queryString, $getParams);
                $cacheKeyData = [
                    'path' => $urlPath,
                    'get' => $getParams,
                ];

//                $cacheAttributes = $methodReflection->getAttributes(CacheAnnotation::class);
//
//                if (!empty($cacheAttributes)) {
//                    $cacheConfig = $cacheAttributes[0]->newInstance();
//                    return $this->handleCache($controllerInstance, $method, $methodArgs, $cacheConfig, $cacheKeyData);
//                }

                $controllerInstance->$method($methodArgs);

            } else {
                throw new \Exception("Route not found: $urlPath", 404);
            }
        } catch (\Exception $e) {
            // Pass the exception to the error handler
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Handle caching for a controller method.
     */
    protected function handleCache(object $controller, string $method, array $args, $cacheConfig, array $cacheKeyData) {
        // Generate a cache key based on the URL path and $_GET parameters
        $key = $cacheConfig->key ?: $this->generateCacheKey($controller, $method, $cacheKeyData);
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }
        $result = call_user_func_array([$controller, $method], $args);
        $this->cache->set($key, $result, $cacheConfig->ttl);

        return $result;
    }

    /**
     * Generate a cache key based on the class, method, and arguments.
     */
    protected function generateCacheKey(object $controller, string $method, array $args): string {
        $class = get_class($controller);

        // If arguments are provided, hash them; otherwise, use only the method name
        $argsHash = !empty($args) ? md5(serialize($args)) : '';

        return "cache_{$class}_{$method}" . ($argsHash ? "_{$argsHash}" : '');
    }

    /**
     * Run middleware.
     */
    private function runMiddleware(array $middleware = []): void {
        // Run global middleware
        foreach ($this->middleware as $middleware) {
            call_user_func($middleware);
        }

        // Run route-specific middleware
        foreach ($middleware as $middlewareClass) {
            call_user_func($middleware);
        }
    }

    /**
     * Serve static files (CSS, JS, images, fonts).
     */
    private function serveStaticFile(string $filePath): void {
        // Define MIME types for common asset files
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            'eot'  => 'application/vnd.ms-fontobject',
        ];

        // Detect file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Set the correct Content-Type header
        if (isset($mimeTypes[$extension])) {
            header("Content-Type: " . $mimeTypes[$extension]);
        } else {
            header("Content-Type: application/octet-stream"); // Default binary type
        }

        // Enable caching
        header("Cache-Control: public, max-age=86400"); // 1 day cache
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT");

        // Output the file content
        readfile($filePath);
        exit;
    }
}
