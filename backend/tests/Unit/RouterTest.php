<?php
namespace Tests\Unit;

use App\Http\Request;
use App\Router;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Support\Router\PassMiddleware;
use Tests\Support\Router\StopMiddleware;
use Tests\Support\Router\TestController;
use Tests\Unit\DataProviders\Router\RouterDataProvider;

class RouterTest extends TestCase
{
    private Router $router;
    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router(new Container());

        http_response_code(200);
    }
    private function dispatch(Router $router, string $method, string $uri): string
    {
        $request = new Request($method, $uri);

        ob_start();

        try {
            $router->dispatch($request);

            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }
    }
    public function test_it_calls_controller_action(): void
    {
        $this->router->get('/ping', [TestController::class, 'index']);
        $output = $this->dispatch($this->router, 'GET', '/ping');

        $this->assertSame(200, http_response_code());

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'message' => 'pong',
                'path' => '/ping',
            ]),
            $output
        );
    }
    public function test_it_returns_not_found_response_when_route_is_not_found(): void
    {
        $output = $this->dispatch($this->router, 'GET', '/ping');
        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('Такой страницы нет', $output);
    }
    public function test_it_does_not_mix_methods():void
    {
        $this->router->post('/ping',[TestController::class, 'store']);
        $output = $this->dispatch($this->router, 'GET', '/ping');

        $this->assertSame(405, http_response_code());
        $this->assertStringContainsString('Метод не разрешен', $output);
    }
    public function test_post_route_can_return_created_status(): void
    {
        $this->router->post('/ping',[TestController::class, 'store']);
        $output = $this->dispatch($this->router, 'POST', '/ping');
        $this->assertSame(201, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'created']), $output
        );
    }
    public function test_middleware_can_pass_request_to_controller(): void
    {
        $this->router->aliasMiddleware('pass', PassMiddleware::class);

        $this->router->get('/ping', [TestController::class, 'index'])->middleware('pass');

        $output = $this->dispatch($this->router, 'GET', '/ping');
        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode(
            [
                'message' => 'pong',
                'path' => '/ping',
            ]
        ), $output);
    }
    public function test_middleware_can_stop_request_before_controller(): void
    {
        $this->router->aliasMiddleware('stop', StopMiddleware::class);

        $this->router->get('/ping', [TestController::class, 'index'])->middleware('stop');

        $output = $this->dispatch($this->router, 'GET', '/ping');
        $this->assertSame(403, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'error'=>'Stopped by middleware'
            ]),$output);
    }
    public function test_dispatch_calls_correct_route_within_multiple_existing_routes(): void
    {
        $this->router->get('/ping',[TestController::class, 'index']);
        $this->router->get('/about',[TestController::class, 'about']);

        $output = $this->dispatch($this->router, 'GET', '/about');

        $this->assertSame(200, http_response_code());

        $this->assertJsonStringEqualsJsonString(json_encode(
            ['message' => 'about',
                'path' => '/about'
            ]
        ), $output);
    }

    public function test_it_dispatches_delete_routes(): void
    {
        $this->router->delete('/ping',[TestController::class, 'destroy']);
        $output = $this->dispatch($this->router, 'DELETE', '/ping');

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString(json_encode(
            ['message' => 'deleted']
        ), $output);
    }

    #[DataProviderExternal(RouterDataProvider::class, 'pageNotFoundCases')]
    public function test_it_throws_page_not_found_message(string $requestMethod, string $uri) :void
    {
        $this->router->post('/ping',[TestController::class, 'store']);
        $this->router->get('/ping',[TestController::class, 'index']);
        $this->router->delete('/ping', [TestController::class, 'destroy']);

        $output = $this->dispatch($this->router, $requestMethod, $uri);

        $this->assertSame(404, http_response_code());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Такой страницы нет']),
            $output
        );
    }
    public function test_it_passes_route_parameter_to_controller(): void
    {
        $this->router->get('/cats/{id}', [TestController::class, 'show']);
        $output = $this->dispatch($this->router, 'GET', '/cats/13');

        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['id' => '13']),
            $output
        );
    }
    public function test_it_passes_many_parameters_to_controller(): void
    {
        $this->router->get('/users/{userId}/cats/{catId}', [TestController::class, 'showForUser']);
        $output = $this->dispatch($this->router, 'GET', '/users/5/cats/13');

        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['user_id' => '5', 'cat_id' => '13']),
            $output
        );
    }
    public function test_it_ignores_useless_slash(): void
    {
        $this->router->get('/cats/{id}', [TestController::class, 'show']);
        $output = $this->dispatch($this->router, 'GET', '/cats/13/');

        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['id' => '13']),
            $output
        );
    }
    public function test_it_decodes_route_parameter(): void
    {
        $this->router->get('/cats/{name}', [TestController::class, 'showByName']);
        $output = $this->dispatch($this->router, 'GET', '/cats/big%20gato');

        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode(['name' => 'big gato']), $output);
    }

    public function test_route_does_not_match_when_segment_count_differs(): void
    {
        $this->router->get('/cats/{id}', [TestController::class, 'show']);
        $output = $this->dispatch($this->router, 'GET', '/cats/13/edit');

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('error', $output);
    }

    public function test_unknown_middleware_stops_request(): void
    {
        $this->router->get('/ping', [TestController::class, 'index'])->middleware('missing');
        $output = $this->dispatch($this->router, 'GET', '/ping');

        $this->assertSame(500, http_response_code());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Middleware не найден']),
            $output
        );
        $this->assertStringNotContainsString('pong', $output);
    }

    public function test_query_string_does_not_affect_route_matching(): void
    {
        $this->router->get('/cats/{id}', [TestController::class, 'show']);
        $output = $this->dispatch($this->router, 'GET', '/cats/13?sort=name');

        $this->assertSame(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode(['id' => '13']), $output);
    }
}
