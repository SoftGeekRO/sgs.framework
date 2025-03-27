<?php

declare(strict_types=1);

namespace SGS\Http;

class Request {
    private array $queryParams;
    private array $postData;
    private array $server;
    private array $cookies;
    private array $files;
    private array $headers;
    private string $method;
    private string $uri;
    private string $pathInfo;

    public function __construct(
        array $queryParams = [],
        array $postData = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->queryParams = $queryParams;
        $this->postData = $postData;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->headers = $this->parseHeaders($server);
        $this->method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $server['REQUEST_URI'] ?? '/';
        $this->pathInfo = parse_url($this->uri, PHP_URL_PATH) ?: '/';
    }

    public static function createFromGlobals(): self {
        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES
        );
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getPathInfo(): string {
        return $this->pathInfo;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function getPostData(): array {
        return $this->postData;
    }

    public function getServerParams(): array {
        return $this->server;
    }

    public function getCookies(): array {
        return $this->cookies;
    }

    public function getFiles(): array {
        return $this->files;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getHeader(string $name): ?string {
        return $this->headers[strtolower($name)] ?? null;
    }

    private function parseHeaders(array $server): array {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[strtolower($name)] = $value;
            }
        }
        return $headers;
    }
}