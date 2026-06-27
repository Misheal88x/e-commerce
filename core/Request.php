<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $body    = [];
    private array $query   = [];
    private array $params  = [];
    private array $headers = [];
    private array $files   = [];
    private ?array $user   = null;

    private function __construct() {}

    public static function capture(): static
    {
        $instance          = new static();
        $instance->query   = $_GET  ?? [];
        $instance->headers = self::parseHeaders();

        $method      = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if ($method === 'POST') {
            $instance->files = $_FILES ?? [];
            $instance->body  = $instance->parseBody();
        } elseif (in_array($method, ['PUT', 'PATCH'], true)
            && str_contains($contentType, 'multipart/form-data')
        ) {
            [$instance->body, $instance->files] = $instance->parseMultipart($contentType);
        } else {
            $instance->body = $instance->parseBody();
        }

        return $instance;
    }

    private static function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        return $headers;
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

        return $_POST ?? [];
    }

    /**
     * Parses multipart/form-data from php://input for non-POST methods.
     * Returns [fields, files] where files mimic the $_FILES structure.
     */
    private function parseMultipart(string $contentType): array
    {
        if (!preg_match('/boundary=(.+)$/i', $contentType, $m)) {
            return [[], []];
        }

        $boundary = trim($m[1], '"');
        $raw      = file_get_contents('php://input');
        $fields   = [];
        $files    = [];

        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(?:--)?/', $raw);
        array_shift($parts); // remove leading empty

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");
            if ($part === '' || $part === '--') {
                continue;
            }

            [$headerBlock, $body] = array_pad(explode("\r\n\r\n", $part, 2), 2, '');
            $body = rtrim($body, "\r\n");

            $headers    = [];
            $headerLines = explode("\r\n", $headerBlock);
            foreach ($headerLines as $line) {
                if (str_contains($line, ':')) {
                    [$hName, $hVal] = explode(':', $line, 2);
                    $headers[strtolower(trim($hName))] = trim($hVal);
                }
            }

            $disposition = $headers['content-disposition'] ?? '';
            if (!preg_match('/name="([^"]+)"/', $disposition, $nm)) {
                continue;
            }
            $name = $nm[1];

            if (preg_match('/filename="([^"]*)"/', $disposition, $fm)) {
                $filename = $fm[1];
                $mimeType = $headers['content-type'] ?? 'application/octet-stream';

                $tmpPath = tempnam(sys_get_temp_dir(), 'php_upload_');
                file_put_contents($tmpPath, $body);

                $files[$name] = [
                    'name'     => $filename,
                    'type'     => $mimeType,
                    'tmp_name' => $tmpPath,
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => strlen($body),
                ];
            } else {
                $fields[$name] = $body;
            }
        }

        return [$fields, $files];
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim($uri, '/');
    }

    public function body(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtoupper($key)] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('AUTHORIZATION', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function isJson(): bool
    {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('ACCEPT', '');
        return str_contains($accept, 'application/json') || str_contains($accept, '*/*');
    }
}
