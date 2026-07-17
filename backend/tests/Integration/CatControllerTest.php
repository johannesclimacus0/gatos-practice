<?php

namespace Tests\Integration;

use App\Controllers\CatController;
use App\Http\Request;
use App\Models\Cat;
use App\Models\User;
use App\Services\CatService;
use App\Validation\CatValidator;
use Tests\Support\DatabaseTestCase;

class CatControllerTest extends DatabaseTestCase
{
    private CatController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CatController(new CatService(new CatValidator()));
        http_response_code(200);
    }

    public function test_index_returns_only_authenticated_users_cats(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $owner->cats()->create(['name' => 'Mine', 'lang' => 'meow']);
        $other->cats()->create(['name' => 'Not mine', 'lang' => 'mew']);

        $data = $this->call('index', $this->request('GET', '/api/cats', $owner));

        $this->assertSame(200, http_response_code());
        $this->assertCount(1, $data['cats']);
        $this->assertSame('Mine', $data['cats'][0]['name']);
    }

    public function test_store_creates_cat(): void
    {
        $user = $this->user('owner@example.com');
        $request = $this->request('POST', '/api/cats', $user, [
            'name' => 'Kisa',
            'lang' => 'meow',
        ]);

        $data = $this->call('store', $request);

        $this->assertSame(201, http_response_code());
        $this->assertSame('Kisa', $data['cat']['name']);
        $this->assertSame($user->id, Cat::query()->first()->user_id);
    }

    public function test_store_returns_validation_errors(): void
    {
        $user = $this->user('owner@example.com');

        $data = $this->call('store', $this->request('POST', '/api/cats', $user, []));

        $this->assertSame(422, http_response_code());
        $this->assertArrayHasKey('fields', $data);
        $this->assertSame(0, Cat::query()->count());
    }

    public function test_show_hides_another_users_cat(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $cat = $other->cats()->create(['name' => 'Private', 'lang' => 'meow']);
        $request = $this->request('GET', "/api/cats/{$cat->id}", $owner);
        $request->setRouteParams(['id' => (string) $cat->id]);

        $data = $this->call('show', $request);

        $this->assertSame(404, http_response_code());
        $this->assertArrayHasKey('error', $data);
    }

    public function test_update_changes_cat(): void
    {
        $user = $this->user('owner@example.com');
        $cat = $user->cats()->create(['name' => 'Old', 'lang' => 'meow']);
        $request = $this->request('PATCH', "/api/cats/{$cat->id}", $user, ['name' => 'New']);
        $request->setRouteParams(['id' => (string) $cat->id]);

        $data = $this->call('update', $request);

        $this->assertSame(200, http_response_code());
        $this->assertSame('New', $data['cat']['name']);
        $this->assertSame('New', $cat->fresh()->name);
    }

    public function test_destroy_deletes_cat(): void
    {
        $user = $this->user('owner@example.com');
        $cat = $user->cats()->create(['name' => 'Kisa', 'lang' => 'meow']);
        $request = $this->request('DELETE', "/api/cats/{$cat->id}", $user);
        $request->setRouteParams(['id' => (string) $cat->id]);

        $data = $this->call('destroy', $request);

        $this->assertSame(200, http_response_code());
        $this->assertSame($cat->id, $data['cat']['id']);
        $this->assertSame(0, Cat::query()->count());
    }

    private function request(string $method, string $url, User $user, ?array $json = null): Request
    {
        $request = new Request($method, $url, $json);
        $request->setAttribute('user', $user);
        return $request;
    }

    private function call(string $method, Request $request): array
    {
        ob_start();
        try {
            $this->controller->$method($request);
            return json_decode(ob_get_clean(), true);
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }

    private function user(string $email): User
    {
        return User::query()->create([
            'email' => $email,
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role_id' => 1,
        ]);
    }
}
