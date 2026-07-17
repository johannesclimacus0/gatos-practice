<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\AuthService;
use Tests\Support\DatabaseTestCase;

class AuthServiceRegisterTest extends DatabaseTestCase
{
    public function test_it_registers_and_logs_in_new_user(): void
    {
        $result = (new AuthService())->registerUser('test@example.com', 'password');

        $this->assertNotNull($result);
        $this->assertSame('test@example.com', $result->email);
        $this->assertTrue(password_verify('password', $result->password));
        $this->assertSame($result->id, $_SESSION['user_id']);
        $this->assertSame(1, User::query()->count());
    }

    public function test_it_does_not_register_duplicate_email(): void
    {
        User::query()->create([
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role_id' => 1,
        ]);

        $result = new AuthService()->registerUser('test@example.com', 'password');

        $this->assertNull($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertSame(1, User::query()->count());
    }

    public function test_it_creates_remember_token_when_registering(): void
    {
        $result = new AuthService()->registerUser('new@example.com', 'password', true);

        $storedHash = $result?->fresh()->remember_token;
        $this->assertIsString($storedHash);
        $this->assertSame(64, strlen($storedHash));
        $this->assertSame($result?->id, $_SESSION['user_id']);
    }
}
