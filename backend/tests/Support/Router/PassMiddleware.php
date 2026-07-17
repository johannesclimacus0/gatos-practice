<?php

namespace Tests\Support\Router;

use App\Http\Request;
use Closure;

class PassMiddleware
{
    public function handle(Request $request, Closure $next): void
    {
        $next($request);
    }
}