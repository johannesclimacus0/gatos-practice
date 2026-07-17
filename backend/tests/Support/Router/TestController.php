<?php

namespace Tests\Support\Router;

use App\Controller;
use App\Http\Request;

class TestController extends Controller
{
    public function index(Request $request): void
    {
        $this->json([
            'message' => 'pong',
            'path' => $request->path(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->json([
            'message' => 'created',
        ], 201);
    }
    public function about(Request $request): void
    {
        $this->json([
            'message' => 'about',
            'path' => $request->path(),
        ]);
    }
    public function destroy(Request $request): void
    {
        $this->json([
            'message' => 'deleted',
        ], 200);
    }
    public function show(Request $request): void
    {
        $this->json([
            'id' => $request->route('id'),
        ]);
    }
    public function showForUser(Request $request): void
    {
        $this->json([
            'user_id' => $request->route('userId'),
            'cat_id' => $request->route('catId'),
        ]);
    }
    public function showByName(Request $request): void
    {
        $this->json([
            'name' => $request->route('name'),
        ]);
    }
}
