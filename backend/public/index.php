<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\App;
use App\Http\Request;
use App\Router;
use Illuminate\Container\Container;

$origins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$container = new Container();
$router = new Router($container);

require_once __DIR__ . '/../routes/api.php';
require_once __DIR__ . '/../routes/web.php';

$request = new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
new App($container, $router, $request)->boot()->run();
