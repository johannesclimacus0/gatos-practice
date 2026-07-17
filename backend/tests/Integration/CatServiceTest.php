<?php

namespace Tests\Integration;

use App\Exceptions\CatNotFoundException;
use App\Models\Cat;
use App\Models\User;
use App\Services\CatService;
use App\Validation\CatValidator;
use Tests\Support\DatabaseTestCase;

class CatServiceTest extends DatabaseTestCase
{
    private CatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CatService(new CatValidator());
    }

    public function test_it_creates_cat_for_user(): void
    {
        $user = $this->user('owner@example.com');

        $cat = $this->service->createForUser($user, [
            'name' => ' Kisa ',
            'lang' => ' meow ',
            'user_id' => 999,
        ]);

        $this->assertSame($user->id, $cat->user_id);
        $this->assertSame('Kisa', $cat->name);
        $this->assertSame('meow', $cat->lang);
        $this->assertDatabaseCatCount(1);
    }

    public function test_list_contains_only_users_cats(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $owner->cats()->create(['name' => 'Owner cat', 'lang' => 'meow']);
        $other->cats()->create(['name' => 'Other cat', 'lang' => 'mew']);

        $cats = $this->service->listForUser($owner);

        $this->assertCount(1, $cats);
        $this->assertSame('Owner cat', $cats->first()->name);
    }

    public function test_user_cannot_find_another_users_cat(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $cat = $other->cats()->create(['name' => 'Private', 'lang' => 'meow']);

        $this->expectException(CatNotFoundException::class);
        $this->service->findForUser($owner, $cat->id);
    }

    public function test_it_updates_users_cat(): void
    {
        $user = $this->user('owner@example.com');
        $cat = $user->cats()->create(['name' => 'Old', 'lang' => 'meow']);

        $updated = $this->service->updateForUser($user, $cat->id, ['name' => ' New ']);

        $this->assertSame('New', $updated->name);
        $this->assertSame('meow', $updated->lang);
        $this->assertSame('New', $cat->fresh()->name);
    }

    public function test_user_cannot_update_another_users_cat(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $cat = $other->cats()->create(['name' => 'Private', 'lang' => 'meow']);

        try {
            $this->service->updateForUser($owner, $cat->id, ['name' => 'Hacked']);
            $this->fail('Expected CatNotFoundException was not thrown.');
        } catch (CatNotFoundException) {
            $this->assertSame('Private', $cat->fresh()->name);
        }
    }

    public function test_it_deletes_users_cat(): void
    {
        $user = $this->user('owner@example.com');
        $cat = $user->cats()->create(['name' => 'Kisa', 'lang' => 'meow']);

        $deleted = $this->service->deleteForUser($user, $cat->id);

        $this->assertSame($cat->id, $deleted->id);
        $this->assertNull(Cat::query()->find($cat->id));
    }

    private function user(string $email): User
    {
        return User::query()->create([
            'email' => $email,
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role_id' => 1,
        ]);
    }

    private function assertDatabaseCatCount(int $count): void
    {
        $this->assertSame($count, Cat::query()->count());
    }
}
