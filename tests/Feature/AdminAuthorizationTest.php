<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_with_admin_as_the_intended_destination(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('url.intended', route('admin.index'));
    }

    public function test_user_is_forbidden_from_accessing_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.index'));

        $response->assertNoContent();
    }
}
