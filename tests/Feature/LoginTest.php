<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_form(): void
    {
        $this->withoutVite();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_a_regenerated_session_and_profile_redirect(): void
    {
        $user = User::factory()->create();
        $this->withSession(['cart' => 'retained']);
        $session = $this->app['session.store'];
        $sessionId = $session->getId();
        $token = $session->token();

        $response = $this->withCookie(config('session.cookie'), $sessionId)
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('cart', 'retained');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, $session->getId());
        $this->assertNotSame($token, $session->token());
    }

    public function test_login_redirects_to_the_intended_destination(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['url.intended' => url('/feature')])
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(url('/feature'));
        $response->assertSessionMissing('url.intended');
        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('invalidCredentials')]
    public function test_invalid_credentials_keep_the_user_guest_and_flash_only_email(string $email, string $password): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
            'extra' => 'not-flashed',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah. Silakan coba lagi.',
        ]);
        $response->assertSessionHas('_old_input', ['email' => $email]);
        $this->assertGuest();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidCredentials(): array
    {
        return [
            'wrong password' => ['user@example.com', 'wrong-password'],
            'unknown email' => ['unknown@example.com', 'password'],
        ];
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), []);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email wajib diisi.',
            'password' => 'Password wajib diisi.',
        ]);
        $this->assertGuest();
    }

    public function test_login_rejects_malformed_email_and_non_string_password(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'invalid-email',
            'password' => ['password'],
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Format email tidak valid.',
            'password' => 'Password harus berupa teks.',
        ]);
        $response->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_view_login_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_cannot_login_as_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->post(route('login.store'), [
            'email' => $otherUser->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $response = $this->get(route('profile.index'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('url.intended', route('profile.index'));
        $this->assertGuest();
    }
}
