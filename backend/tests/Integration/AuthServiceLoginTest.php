<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\AuthService;
use Tests\Support\DatabaseTestCase;

class AuthServiceLoginTest extends DatabaseTestCase
{
    public function test_it_logs_in_existing_user_with_correct_password(): void
    {
        $user = $this->createUser('test@example.com', 'password');

        $result = new AuthService()->loginAttempt('test@example.com', 'password');

        $this->assertSame($user->id, $result?->id);
        $this->assertSame($user->id, $_SESSION['user_id']);
    }

    public function test_it_rejects_unknown_email(): void
    {
        $result = new AuthService()->loginAttempt('test@example.com', 'password');

        $this->assertNull($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_it_rejects_wrong_password(): void
    {
        $this->createUser('test@example.com', 'correct');

        $result = (new AuthService())->loginAttempt('test@example.com', 'wrong');

        $this->assertNull($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_it_returns_current_user_from_session(): void
    {
        $user = $this->createUser('test@example.com', 'password');
        $_SESSION['user_id'] = $user->id;

        $result = (new AuthService())->currentUser();

        $this->assertSame($user->id, $result?->id);
        $this->assertSame('test@example.com', $result?->email);
    }

    public function test_it_stores_hashed_remember_token(): void
    {
        $user = $this->createUser('test@example.com', 'password');

        (new AuthService())->loginAttempt('test@example.com', 'password', true);

        $storedHash = $user->fresh()->remember_token;
        $this->assertIsString($storedHash);
        $this->assertSame(64, strlen($storedHash));
        $this->assertSame($user->id, $_SESSION['user_id']);
    }

    public function test_it_logs_in_from_valid_remember_cookie(): void
    {
        $plainToken = 'plain-token';
        $user = $this->createUser('test@example.com', 'password', hash('sha256', $plainToken));
        $_COOKIE['remember_token'] = $plainToken;

        $result = (new AuthService())->currentUser();

        $this->assertSame($user->id, $result?->id);
        $this->assertSame($user->id, $_SESSION['user_id']);
    }

    private function createUser(
        string $email,
        string $password,
        ?string $rememberToken = null
    ): User {
        return User::query()->create([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'remember_token' => $rememberToken,
            'role_id' => 1,
        ]);
    }
}
