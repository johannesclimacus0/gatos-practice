<?php

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Services\CsrfService;
use Closure;

class CsrfMiddleware
{
    public function __construct(private CsrfService $csrfService){}

    public function handle(Request $request, Closure $next): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$this->csrfService->isValid($token)) {
            Response::json(['error' => 'Неверный CSRF-токен'], 419);
            return;
        }

        $next($request);
    }
}
