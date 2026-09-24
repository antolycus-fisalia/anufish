<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_unauthenticates_invalidates_the_session_and_rotates_the_csrf_token(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession([
            Auth::guard('web')->getName() => $user->getAuthIdentifier(),
            'private_data' => 'must-be-removed',
            '_token' => 'old-csrf-token',
        ]);
        $session = $this->app['session.store'];
        $sessionId = $session->getId();

        $response = $this->withCookie(config('session.cookie'), $sessionId)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionMissing('private_data');
        $response->assertSessionMissing(Auth::guard('web')->getName());
        $this->assertGuest();
        $this->assertNotSame($sessionId, $session->getId());
        $this->assertNotEmpty($session->token());
        $this->assertNotSame('old-csrf-token', $session->token());

        $this->get(route('profile.index'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_logout_and_is_redirected_to_login(): void
    {
        $response = $this->withSession(['guest_data' => 'retained'])
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('guest_data', 'retained');
        $this->assertGuest();
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('logout'));

        $response->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($user);
    }
}
