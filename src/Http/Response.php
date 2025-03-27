<?php declare(strict_types=1);

namespace SGS\Http;

class Response {
    private string $content;
    private int $statusCode;
    private array $headers;

    public function __construct(
        string $content = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_change_key_case($headers);
    }

    public function send(): void {
        $this->sendHeaders();
        $this->sendContent();
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function setHeader(string $name, string $value): self {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    private function sendHeaders(): void {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }

    private function sendContent(): void {
        echo $this->content;
    }

    public static function json(array $data, int $status = 200): self {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    public static function redirect(string $url, int $status = 302): self {
        return new self(
            '',
            $status,
            ['Location' => $url]
        );
    }

    public static function file(string $path, string $mimeType = null): self {
        $content = file_get_contents($path);
        $mimeType = $mimeType ?? mime_content_type($path) ?: 'application/octet-stream';

        return new self(
            $content,
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($content)
            ]
        );
    }
}