<?php

namespace App;

class RouteDefinition
{
    public function __construct(
        public string $method,
        public string $path,
        public array $handler,
        public array $middleware = [],
    )
    {
    }
    public function middleware(array|string $middleware): self{
        if(is_string($middleware)){
            $middleware = [$middleware];
        }
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
}