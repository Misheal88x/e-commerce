<?php

declare(strict_types=1);

namespace Core;

class Response
{
    private int    $status  = 200;
    private array  $headers = [];
    private mixed  $body    = null;

    public function __construct(mixed $body = null, int $status = 200, array $headers = [])
    {
        $this->body    = $body;
        $this->status  = $status;
        $this->headers = $headers;
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): static
    {
        $response = new static($data, $status, $headers);
        $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

    public static function empty(int $status = 204): static
    {
        return new static(null, $status);
    }

    public function withHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function send(): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->body !== null) {
            echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}
