<?php

namespace App\Controllers;

use App\Controller;
use App\Exceptions\CatNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Request;
use App\Models\Cat;
use App\Models\User;
use App\Services\CatService;

class CatController extends Controller
{
    public function __construct(private CatService $catService){}

    public function index(Request $request): void
    {
        $cats = $this->catService->listForUser($this->user($request));

        $this->json([
            'cats' => $cats->map(fn (Cat $cat) => $this->catData($cat))->all()
        ]);
    }

    public function store(Request $request): void
    {
        try {
            $cat = $this->catService->createForUser($this->user($request), $request->json()
            );
        } catch (ValidationException $exception) {
            $this->validationError($exception);
            return;
        }

        $this->json([
            'message' => 'Кот добавлен',
            'cat' => $this->catData($cat),
        ], 201);
    }

    public function show(Request $request): void
    {
        try {
            $cat = $this->catService->findForUser(
                $this->user($request),
                (int) $request->route('id')
            );
        } catch (CatNotFoundException $exception) {
            $this->notFound($exception);
            return;
        }

        $this->json(['cat' => $this->catData($cat)]);
    }

    public function update(Request $request): void
    {
        try {
            $cat = $this->catService->updateForUser(
                $this->user($request),
                (int) $request->route('id'),
                $request->json()
            );
        } catch (ValidationException $exception) {
            $this->validationError($exception);
            return;
        } catch (CatNotFoundException $exception) {
            $this->notFound($exception);
            return;
        }

        $this->json(['message' => 'Кот обновлён', 'cat' => $this->catData($cat)]);
    }

    public function destroy(Request $request): void
    {
        try {
            $cat = $this->catService->deleteForUser(
                $this->user($request),
                (int) $request->route('id')
            );
        } catch (CatNotFoundException $exception) {
            $this->notFound($exception);
            return;
        }

        $this->json(['message' => 'Кот удалён', 'cat' => $this->catData($cat)]);
    }

    private function user(Request $request): User
    {
        return $request->attribute('user');
    }

    private function validationError(ValidationException $exception): void
    {
        $this->json([
            'error' => 'Данные не прошли проверку',
            'fields' => $exception->errors(),
        ], 422);
    }

    private function notFound(CatNotFoundException $exception): void
    {
        $this->json(['error' => $exception->getMessage()], 404);
    }

    private function catData(Cat $cat): array
    {
        return [
            'id' => $cat->id,
            'name' => $cat->name,
            'lang' => $cat->lang,
        ];
    }
}
