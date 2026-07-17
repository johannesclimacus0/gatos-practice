<?php

namespace Tests\Integration;

use App\Controllers\AuthController;
use App\Http\Request;
use App\Services\AuthService;
use Tests\Support\DatabaseTestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthControllerTest extends DatabaseTestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new AuthController(
            new AuthService()
        );

        http_response_code(200);
    }

    public function test_it_registers_user(): void
    {
        $request = new Request(
            'POST',
            '/api/register',
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'password_repeat' => 'password',
                'remember_me' => false,
            ]
        );

        $output = $this->callController(
            'register',
            $request
        );

        $this->assertSame(
            201,
            http_response_code()
        );
        $user = User::query()
            ->where('email', 'test@example.com')
            ->first();

        $data = json_decode($output, true);

        $this->assertSame(201, http_response_code());
        $this->assertSame('/cats', $data['redirectTo']);
        $this->assertSame('test@example.com', $data['user']['email']);
        $this->assertSame(1, $data['user']['id']);
        $this->assertArrayNotHasKey('password', $data['user']);
        $this->assertArrayNotHasKey('remember_token', $data['user']);

        $this->assertSame(1, User::query()->count());



        $this->assertNotNull($user);
        $this->assertTrue(password_verify('password', $user->password));
        $this->assertSame($user->id, $_SESSION['user_id']);
    }

    public function test_registration_rejects_empty_request(): void
    {
        $output = $this->callController('register', new Request('POST', '/api/register', []));
        $data = json_decode($output, true);

        $this->assertSame(400, http_response_code());
        $this->assertArrayHasKey('error', $data);
        $this->assertSame(0, User::query()->count());
    }

    public static function invalidRegistrationData(): array
    {
        return [
            'empty email' => [[
                'email' => '',
                'password' => 'password',
                'password_repeat' => 'password',
            ]],
            'invalid email' => [[
                'email' => 'not-an-email',
                'password' => 'password',
                'password_repeat' => 'password',
            ]],
            'empty password' => [[
                'email' => 'test@example.com',
                'password' => '',
                'password_repeat' => '',
            ]],
            'short password' => [[
                'email' => 'test@example.com',
                'password' => '12345',
                'password_repeat' => '12345',
            ]],
            'password mismatch' => [[
                'email' => 'test@example.com',
                'password' => 'password',
                'password_repeat' => 'different',
            ]],
        ];
    }

    #[DataProvider('invalidRegistrationData')]
    public function test_registration_validates_input(array $requestData): void
    {
        $output = $this->callController(
            'register',
            new Request('POST', '/api/register', $requestData)
        );
        $data = json_decode($output, true);

        $this->assertSame(422, http_response_code());
        $this->assertArrayHasKey('error', $data);
        $this->assertSame(0, User::query()->count());
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $this->createUser('taken@example.com', 'password');
        $request = new Request('POST', '/api/register', [
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_repeat' => 'password',
        ]);

        $data = json_decode($this->callController('register', $request), true);

        $this->assertSame(409, http_response_code());
        $this->assertArrayHasKey('error', $data);
        $this->assertSame(1, User::query()->count());
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_it_logs_in_user(): void
    {
        $user = $this->createUser('test@example.com', 'password');
        $request = new Request('POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember_me' => false,
        ]);

        $data = json_decode($this->callController('login', $request), true);

        $this->assertSame(200, http_response_code());
        $this->assertSame($user->id, $data['user']['id']);
        $this->assertSame($user->email, $data['user']['email']);
        $this->assertSame('/cats', $data['redirectTo']);
        $this->assertSame($user->id, $_SESSION['user_id']);
        $this->assertArrayNotHasKey('password', $data['user']);
    }

    public function test_login_rejects_empty_request(): void
    {
        $data = json_decode($this->callController(
            'login',
            new Request('POST', '/api/login', [])
        ), true);

        $this->assertSame(400, http_response_code());
        $this->assertArrayHasKey('error', $data);
    }

    public function test_login_requires_email_and_password(): void
    {
        $data = json_decode($this->callController(
            'login',
            new Request('POST', '/api/login', ['email' => 'test@example.com'])
        ), true);

        $this->assertSame(422, http_response_code());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->createUser('test@example.com', 'correct-password');
        $request = new Request('POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $data = json_decode($this->callController('login', $request), true);

        $this->assertSame(401, http_response_code());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_current_user_returns_authenticated_user(): void
    {
        $user = $this->createUser('test@example.com', 'password');
        $_SESSION['user_id'] = $user->id;

        $data = json_decode($this->callController('currentUser'), true);

        $this->assertSame(200, http_response_code());
        $this->assertSame($user->id, $data['user']['id']);
        $this->assertSame($user->email, $data['user']['email']);
    }

    public function test_current_user_rejects_guest(): void
    {
        $data = json_decode($this->callController('currentUser'), true);

        $this->assertSame(401, http_response_code());
        $this->assertArrayHasKey('error', $data);
    }

    public function test_logout_clears_session_and_remember_token(): void
    {
        $user = $this->createUser('test@example.com', 'password', hash('sha256', 'token'));
        $_SESSION['user_id'] = $user->id;
        $_COOKIE['remember_token'] = 'token';

        $data = json_decode($this->callController('logout'), true);

        $this->assertSame(200, http_response_code());
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertNull($user->fresh()->remember_token);
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

    private function callController(
        string $method,
        ?Request $request = null
    ): string {
        ob_start();

        try {
            if ($request === null) {
                $this->controller->$method();
            } else {
                $this->controller->$method($request);
            }

            return ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }
}
