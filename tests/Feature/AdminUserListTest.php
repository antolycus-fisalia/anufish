<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
            'status' => 'aktif',
        ], $attributes));
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'status' => 'aktif',
        ], $attributes));
    }

    public function test_guest_cannot_access_admin_user_list(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_user_list(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_user_list(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser([
            'name' => 'Nadya',
            'email' => 'nadya@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Nadya')
            ->assertSee('nadya@example.com');
    }

    public function test_admin_can_search_users(): void
    {
        $admin = $this->makeAdmin();

        $matched = $this->makeUser([
            'name' => 'Nadya',
            'email' => 'nadya@example.com',
        ]);

        $other = $this->makeUser([
            'name' => 'Taylor',
            'email' => 'taylor@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'nadya']))
            ->assertOk()
            ->assertSee('Nadya')
            ->assertSee('nadya@example.com')
            ->assertDontSee('Taylor')
            ->assertDontSee('taylor@example.com');
    }

    public function test_admin_sees_empty_state_when_search_has_no_result(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'pengguna-tidak-ada']))
            ->assertOk()
            ->assertSee(__('admin_users.empty'));
    }

    public function test_search_treats_like_wildcards_as_literal_characters(): void
    {
        $admin = $this->makeAdmin();
        $matched = $this->makeUser(['name' => 'Fish !_100%']);
        $this->makeUser(['name' => 'Fish !X1000']);

        $this->actingAs($admin)
            ->getJson(route('admin.users.index', ['search' => '!_100%']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matched->id);
    }

    public function test_admin_user_list_is_paginated(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->count(11)->create([
            'role' => 'user',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) {
            return $users->count() === 10
                && $users->total() >= 11
                && $users->perPage() === 10;
        });
    }

    public function test_admin_can_view_user_detail(): void
    {
        $admin = $this->makeAdmin();

        $user = $this->makeUser([
            'name' => 'Nadya',
            'email' => 'nadya@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee('Nadya')
            ->assertSee('nadya@example.com')
            ->assertDontSee('password')
            ->assertDontSee('remember_token');
    }

    public function test_non_admin_cannot_view_user_detail(): void
    {
        $adminUser = $this->makeUser();
        $target = $this->makeUser();

        $this->actingAs($adminUser)
            ->get(route('admin.users.show', $target))
            ->assertForbidden();
    }

    public function test_admin_gets_404_for_missing_user_detail(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get('/admin/users/999999')
            ->assertNotFound();
    }

    public function test_json_response_does_not_expose_sensitive_fields(): void
    {
        $admin = $this->makeAdmin();

        $user = $this->makeUser([
            'name' => 'Nadya',
            'email' => 'nadya@example.com',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.users.show', $user))
            ->assertOk()
            ->assertJsonPath('data.name', 'Nadya')
            ->assertJsonPath('data.email', 'nadya@example.com')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }
}
