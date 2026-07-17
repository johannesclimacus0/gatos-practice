<?php

namespace App\Http;

class Request
{
    private array $routeParams = [];
    private array $attributes = [];

    public function __construct
    (
        public string $method,
        public string $url,
        private ?array $jsonBody = null
    )
    {
        $this->method = strtoupper($this->method);
    }

    public function path(): string
    {
        return parse_url($this->url, PHP_URL_PATH);
    }

    public function json():array
    {
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }
    public function routeParams(): array
    {
        return $this->routeParams;
    }
    public function query(string $key, mixed $defaultValue = null): mixed
    {
        return $_GET[$key] ?? $defaultValue;
    }
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
