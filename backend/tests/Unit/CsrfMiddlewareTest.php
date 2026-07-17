<?php

namespace Tests\Unit;

use App\Http\Middleware\CsrfMiddleware;
use App\Http\Request;
use App\Services\CsrfService;
use PHPUnit\Framework\TestCase;

class CsrfMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        parent::tearDown();
    }

    public function test_it_passes_request_with_valid_token(): void
    {
        $service = new CsrfService();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $service->token();
        $nextCalled = false;

        new CsrfMiddleware($service)->handle(
            new Request('POST', '/api/cats'),
            function () use (&$nextCalled): void {
                $nextCalled = true;
            }
        );

        $this->assertTrue($nextCalled);
        $this->assertSame(200, http_response_code());
    }

    public function test_it_rejects_request_without_token(): void
    {
        $nextCalled = false;
        ob_start();

        new CsrfMiddleware(new CsrfService())->handle(
            new Request('POST', '/api/cats'),
            function () use (&$nextCalled): void {
                $nextCalled = true;
            }
        );

        $data = json_decode(ob_get_clean(), true);

        $this->assertSame(419, http_response_code());
        $this->assertFalse($nextCalled);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_it_rejects_invalid_token(): void
    {
        $service = new CsrfService();
        $service->token();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'invalid';
        ob_start();

        new CsrfMiddleware($service)->handle(
            new Request('DELETE', '/api/cats/1'),
            static function (): void {}
        );

        ob_end_clean();

        $this->assertSame(419, http_response_code());
    }
}
