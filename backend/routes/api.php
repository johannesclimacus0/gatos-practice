<?php
use App\Controllers\AuthController;
use App\Controllers\CatController;
use App\Controllers\CatFactsController;
use App\Controllers\CsrfController;

$router->get('/api/csrf', [CsrfController::class, 'token']);

$router
    ->post('/api/login', [AuthController::class, 'login'])
    ->middleware(['guest', 'csrf']);
$router
    ->post('/api/register', [AuthController::class, 'register'])
    ->middleware(['guest', 'csrf']);
$router
    ->get('/api/me', [AuthController::class, 'currentUser'])
    ->middleware('auth');
$router
    ->post('/api/logout', [AuthController::class, 'logout'])
    ->middleware(['auth', 'csrf']);



$router
    ->get('/api/cats', [CatController::class, 'index'])
    ->middleware('auth');

$router
    ->post('/api/cats', [CatController::class, 'store'])
    ->middleware(['auth', 'csrf']);

$router
    ->get('/api/cats/{id}', [CatController::class, 'show'])
    ->middleware('auth');

$router
    ->put('/api/cats/{id}', [CatController::class, 'update'])
    ->middleware(['auth', 'csrf']);

$router
    ->patch('/api/cats/{id}', [CatController::class, 'update'])
    ->middleware(['auth', 'csrf']);

$router
    ->delete('/api/cats/{id}', [CatController::class, 'destroy'])
    ->middleware(['auth', 'csrf']);


$router
    ->get('/api/catfacts/{amount}', [CatFactsController::class, 'get']);
$router
    ->get('/api/catfacts/{amount}/{length}', [CatFactsController::class, 'get']);
$router
    ->get('/api/catfacts/', [CatFactsController::class, 'get']);
