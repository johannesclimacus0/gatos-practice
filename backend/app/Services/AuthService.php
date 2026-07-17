<?php

namespace App\Services;
use App\Models\User;

class AuthService
{
    public function loginAttempt(string $email, string $password, bool $remember = false): ?User
    {
        $user = User::query()->where('email', $email)->first();
        if (!$user || !password_verify($password, $user->password)) {
            return null;
        }

        $this->loginById($user->id);
        if ($remember) {
            $this->createRememberToken($user->id);
        }

        return $user;
    }
    public function currentUser(): ?User
    {
        if (isset($_SESSION['user_id'])) {
            return User::query()->find((int) $_SESSION['user_id']);
        }

        return $this->loginFromRememberToken();
    }

    public function registerUser(string $email,string $password, bool $remember = false): ?User
    {
        $existingUser = User::query()->where('email', $email)->first();
        if($existingUser){
            return null;
        }

        $user = User::query()->create([
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role_id' => 1,
            ]);

        $this->loginById($user->id);

        if ($remember) {
            $this->createRememberToken($user->id);
        }

        return $user;
    }

    public function logout(): void
    {
        $user = $this->currentUser();

        if($user){
            $user->remember_token = null;
            $user->save();
        }
        $_SESSION = [];

        if (session_id() !== '') {
            session_destroy();
        }

        $this->forgetRememberToken();
    }

    private function createRememberToken(int $userId):void{
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        User::query()->whereKey($userId)->update(['remember_token' => $tokenHash]);

        setcookie("remember_token", $token, time() + 60 * 60 * 24 * 30);
    }

    private function loginById(int $userId):void{
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    private function loginFromRememberToken(): ?User
    {
        if (empty($_COOKIE['remember_token'])) {
            return null;
        }

        $plainToken = $_COOKIE['remember_token'];
        $tokenHash = hash('sha256', $plainToken);

        $user = User::query()->where('remember_token', $tokenHash)->first();

        if (!$user) {
            $this->forgetRememberToken();
            return null;
        }

        $this->loginById($user->id);

        return $user;
    }

    private function forgetRememberToken(): void
    {
        setcookie('remember_token', '', time() - 60 * 60 * 24 * 30);
    }
}
