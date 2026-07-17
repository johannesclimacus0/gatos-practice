<?php

namespace App;

use App\Http\Request;
use App\Http\Response;
use App\Exceptions\RouteNotFoundException;
use Dotenv\Dotenv;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

class App
{
    private Config $config;

    public function __construct(
        protected Container $container,
        protected Router $router,
        protected Request $request,
    ) {
    }
    public function initDb(array $config): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__."/../");
        $dotenv->load();
        $capsule = new Capsule();
        $capsule->addConnection($config);
        $capsule->setEventDispatcher(new Dispatcher($this->container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
    public function boot(): static
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $this->config = new Config($_ENV);
        $this->initDb($this->config->db);

        return $this;
    }
    public function run(): void
    {
        try {
            $this->router->dispatch($this->request);
        } catch (RouteNotFoundException $e) {
            Response::json([
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
