<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next):void{
        $authService = new AuthService();
        if($authService->currentUser()) {
            Response::json([
                'error' =>'Вы уже вошли в аккаунт',
                'redirectTo'=>'/profile'
            ],403);
            return;
        }
        $next($request);
    }
}