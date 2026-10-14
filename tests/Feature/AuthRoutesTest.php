<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_logout_through_existing_routes(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'aktif']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('homepage'));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_login_keeps_existing_dashboard_response(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin')->assertOk()->assertSee('Dashboard Admin');
    }

    public function test_invalid_credentials_and_inactive_users_cannot_login(): void
    {
        $user = User::factory()->create(['status' => 'diblokir']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_creates_active_user_without_accepting_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Pengguna Baru', 'email' => 'new@example.test',
            'password' => 'testpassword', 'password_confirmation' => 'testpassword',
            'role' => 'admin', 'status' => 'diblokir',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertSame('aktif', $user->status);
        $this->assertTrue(Hash::check('testpassword', $user->password));
        $this->assertGuest();
    }

    public function test_guest_admin_access_redirects_and_user_access_is_forbidden(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/admin')->assertForbidden();
    }

    public function test_login_validation_and_throttle_are_preserved(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', ['email' => 'invalid'])->assertUnprocessable()
                ->assertJsonValidationErrors(['email', 'password']);
        }
        $this->postJson('/login', ['email' => 'invalid'])->assertStatus(429);
    }

    public function test_named_route_paths_remain_unchanged(): void
    {
        foreach ([
            'homepage' => '/', 'profile.show' => '/profile', 'profile.update' => '/profile',
            'fish.search' => '/fish/search', 'fish.show' => '/fish/taxa/93844',
            'fish.extras' => '/fish/taxa/93844/extras', 'admin.dashboard' => '/admin',
            'login.store' => '/login', 'register.store' => '/register', 'logout' => '/logout',
            'locale.update' => '/language',
        ] as $name => $path) {
            $this->assertSame($path, parse_url(route($name, str_starts_with($name, 'fish.') && $name !== 'fish.search' ? ['taxonId' => 93844] : []), PHP_URL_PATH) ?: '/');
        }
    }
}
