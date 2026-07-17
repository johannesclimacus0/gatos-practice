<?php

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;
use Closure;
class AuthMiddleware
{
    public function __construct(private AuthService $authService){}

    public function handle(Request $request, Closure $next): void
    {
        $user = $this->authService->currentUser();

        if (!$user) {
            Response::json([
                'error' => 'Неавторизован',
                'redirectTo' => '/login',
            ], 401);
            return;
        }

        $request->setAttribute('user', $user);
        $next($request);
    }
}
