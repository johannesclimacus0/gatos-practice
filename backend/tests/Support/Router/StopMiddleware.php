<?php

namespace Tests\Support\Router;

use App\Http\Request;
use App\Http\Response;
use Closure;

class StopMiddleware
{
    public function handle(Request $request, Closure $next): void
    {
        Response::json([
            'error' => 'Stopped by middleware',
        ], 403);
    }
}