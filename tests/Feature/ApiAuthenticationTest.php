<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_uses_server_role_and_hides_passwords(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'API User',
            'email' => 'api@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'status' => 'diblokir',
        ])->assertCreated()
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('data.status', 'aktif')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_returns_a_hashed_expiring_token_that_can_be_revoked(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $otherToken = $user->createToken('other-device')->plainTextToken;
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email, 'password' => 'password123',
        ])->assertOk()->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['id', 'email'], 'access_token', 'expires_at'])
            ->assertJsonMissingPath('data.password')
            ->assertHeader('Cache-Control', 'no-store, private');
        $token = $response->json('access_token');
        $stored = PersonalAccessToken::findToken($token);
        $this->assertNotNull($stored);
        $this->assertNotSame($token, $stored->token);
        $this->assertTrue($stored->expires_at->isFuture());

        $this->withToken($token)->get('/api/v1/auth/me')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->post('/api/v1/auth/logout')->assertOk();
        Auth::forgetGuards();
        $this->get('/api/v1/auth/me')->assertUnauthorized();
        $this->assertNotNull(PersonalAccessToken::findToken($otherToken));
        Auth::forgetGuards();
        $this->withToken($otherToken)->get('/api/v1/auth/me')->assertOk();
    }

    public function test_bad_credentials_and_blocked_accounts_cannot_create_tokens(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email, 'password' => 'wrong',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
        $user->update(['status' => 'diblokir']);
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email, 'password' => 'password123',
        ])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_expired_tokens_and_subsequently_blocked_users_are_rejected(): void
    {
        $user = User::factory()->create();
        $expired = $user->createToken('expired', ['*'], now()->subMinute())->plainTextToken;
        $this->withToken($expired)->get('/api/v1/profile')->assertUnauthorized();
        Auth::forgetGuards();
        $token = $user->createToken('active')->plainTextToken;
        $user->update(['status' => 'diblokir']);
        $this->withToken($token)->get('/api/v1/profile')->assertForbidden();
    }

    public function test_api_errors_are_json_even_without_an_accept_header(): void
    {
        $this->get('/api/v1/profile')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/v1/does-not-exist')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->post('/api/v1/auth/register', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);
        $this->withHeader('Accept-Language', 'en')->post('/api/v1/auth/register', [])
            ->assertJsonPath('errors.name.0', 'Name is required.');
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'missing@example.test', 'password' => 'wrong',
            ])->assertUnprocessable();
        }
        $this->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.test', 'password' => 'wrong',
        ])->assertTooManyRequests();
    }

    public function test_api_health_checks_are_available_without_login(): void
    {
        $this->get('/api/v1/up')->assertOk()->assertJsonPath('status', 'up');
        $this->get('/api/v1/ready')->assertOk()->assertJsonPath('status', 'ready');
    }

    public function test_api_requires_a_bearer_token_even_with_an_authenticated_web_guard(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/api/v1/profile')->assertUnauthorized();
    }

    public function test_api_rate_limit_is_shared_between_tokens_for_the_same_user(): void
    {
        $user = User::factory()->create();
        $this->withToken($user->createToken('first')->plainTextToken);
        for ($i = 0; $i < 60; $i++) {
            $this->get('/api/v1/auth/me')->assertOk();
        }
        Auth::forgetGuards();
        $this->withToken($user->createToken('second')->plainTextToken)
            ->get('/api/v1/auth/me')->assertTooManyRequests();
        Auth::forgetGuards();
        $other = User::factory()->create();
        $this->withToken($other->createToken('other')->plainTextToken)
            ->get('/api/v1/auth/me')->assertOk();
    }
}
