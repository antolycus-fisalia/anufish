<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApplicationShellTest extends TestCase
{
    public function test_user_shell_has_navigation_and_post_logout_without_admin_access(): void
    {
        $this->actingAs(new User(['name' => 'Pengguna', 'role' => 'user']))
            ->get('/')
            ->assertOk()
            ->assertSee('Navigasi utama')
            ->assertSee('Buka navigasi')
            ->assertSee('Jelajahi Ikan')
            ->assertSee('method="POST"', false)
            ->assertSee(route('logout'))
            ->assertDontSee('Dashboard Admin');
    }

    public function test_admin_shell_has_admin_navigation(): void
    {
        $this->actingAs(new User(['name' => 'Admin', 'role' => 'admin']))
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee(route('admin.dashboard'));
    }

    public function test_user_cannot_access_admin_destination(): void
    {
        $this->actingAs(new User(['name' => 'Pengguna', 'role' => 'user']))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
