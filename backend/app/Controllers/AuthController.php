<?php

namespace App\Controllers;

use App\Controller;
use App\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService){}

    public function login(Request $request): void
    {
        $data = $request->json();

        if (!$data) {
            $this->json([
                'error' => 'Некорректный запрос',
            ], 400);
            return;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $remember = (bool) ($data['remember_me'] ?? false);

        if ($email === '' || $password === '') {
            $this->json([
                'error' => 'Email и пароль обязательны',
            ], 422);
            return;
        }

        $user = $this->authService->loginAttempt($email, $password, $remember);

        if (!$user) {
            $this->json([
                'error' => 'Неверный email или пароль',
            ], 401);
            return;
        }


        $this->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'redirectTo' => '/cats',
            'message' => 'Успешный вход',
        ]);
    }

    public function currentUser(): void
    {
        $user = $this->authService->currentUser();

        if (!$user) {
            $this->json([
                'error' => 'Не авторизован',
            ], 401);
            return;
        }

        $this->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
    }

    public function register(Request $request): void
    {
        $data = $request->json();

        if (!$data) {
            $this->json([
                'error' => 'Некорректный запрос',
            ], 400);
            return;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_repeat'] ?? '';
        $remember = (bool) ($data['remember_me'] ?? false);

        if ($email === '') {
            $this->json([
                'error' => 'Email обязателен',
            ], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'error' => 'Некорректный формат email',
            ], 422);
            return;
        }

        if ($password === '') {
            $this->json([
                'error' => 'Пароль обязателен',
            ], 422);
            return;
        }

        if (strlen($password) < 6) {
            $this->json([
                'error' => 'Пароль должен содержать минимум 6 символов',
            ], 422);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->json([
                'error' => 'Пароли не совпадают',
            ], 422);
            return;
        }

        $user = $this->authService->registerUser($email, $password, $remember);

        if (!$user) {
            $this->json([
                'error' => 'Аккаунт с таким email уже существует',
            ], 409);
            return;
        }

        $this->json([
            'message' => 'Регистрация успешна',
            'redirectTo' => '/cats',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function logout(): void
    {
        $this->authService->logout();

        $this->json([
            'message' => 'Вы вышли из аккаунта',
        ]);
    }
}
