<?php

namespace SGS\Router;

class Route {
    private string $name;
    private array $middleware = [];

    public function __construct(private string $method, private string $path, private string $controller, private string $action, private array $params = []) {
        $this->validateController();
    }

    private function validateController(): void {
        if (!class_exists($this->controller)) {
            throw new \InvalidArgumentException("Controller {$this->controller} does not exist");
        }

        if (!method_exists($this->controller, $this->action)) {
            throw new \InvalidArgumentException("Action {$this->action} does not exist in controller {$this->controller}");
        }
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function middleware(array $middleware): self {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    // Getters
    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getController(): string {
        return $this->controller;
    }

    public function getAction(): string {
        return $this->action;
    }

    public function getName(): ?string {
        return $this->name ?? null;
    }

    public function getMiddleware(): array {
        return $this->middleware;
    }
}