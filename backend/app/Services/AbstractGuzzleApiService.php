<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class AbstractGuzzleApiService
{
    abstract protected function baseUrl(): string;

    protected function timeout(): int
    {
        return 5;
    }

    protected function retryAttempts(): int
    {
        return 3;
    }

    protected function retryStatusCodes(): array
    {
        return [429, 500, 502, 503, 504];
    }

    protected function client(): Client
    {
        $stack = HandlerStack::create();
        $stack->push($this->retryMiddleware());

        return new Client([
            'base_uri' => $this->baseUrl(),
            'timeout' => $this->timeout(),
            'handler' => $stack,
        ]);
    }

    protected function get(string $path, array $query = []): ?array
    {
        return $this->request('GET', $path, query: $query);
    }

    protected function post(string $path, array $data = [], array $query = []): ?array
    {
        return $this->request('POST', $path, query: $query, data: $data);
    }

    protected function request(string $method, string $path, array $query = [], array $data = []): ?array
    {
        try {
            $response = $this->client()->request($method, $path, [
                'query' => $query,
                'json' => $data,
                'http_errors' => false,
            ]);
        } catch (\Throwable) {
            return null;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            return null;
        }

        $content = json_decode($response->getBody()->getContents(), true);
        return is_array($content) ? $content : null;
    }

    protected function retryMiddleware(): callable
    {
        return Middleware::retry(
            function (int $retries, RequestInterface $request, ?ResponseInterface $response = null, ?Throwable $exception = null): bool
            {
                if ($retries >= $this->retryAttempts()) {
                    return false;
                }
                if ($exception instanceof ConnectException) {
                    return true;
                }
                if ($response === null) {
                    return false;
                }

                return in_array(
                    $response->getStatusCode(),
                    $this->retryStatusCodes(),
                    true
                );
            }
        );
    }
}
