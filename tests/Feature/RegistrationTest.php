<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_form(): void
    {
        $this->withoutVite();

        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertViewIs('auth.register');
    }

    public function test_registration_stores_a_hashed_password_and_redirects_to_login_as_guest(): void
    {
        $response = $this->post(route('register.store'), [
            'nama' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'role' => 'admin',
            'status' => 'diblokir',
            'foto_profil' => 'unexpected.jpg',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'nama' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'role' => 'user',
            'status' => 'aktif',
            'foto_profil' => null,
        ]);

        $user = User::where('email', 'baru@example.com')->firstOrFail();

        $this->assertNotSame('password-baru', $user->password);
        $this->assertTrue(Hash::check('password-baru', $user->password));
    }

    public function test_registration_rejects_an_existing_email(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('register'))->post(route('register.store'), [
            'nama' => 'Pengguna Baru',
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_registration_requires_name_email_and_password(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), []);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'nama' => 'Nama wajib diisi.',
            'email' => 'Email wajib diisi.',
            'password' => 'Password wajib diisi.',
        ]);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    /**
     * @param  array<string, string|array<int, string>>  $input
     */
    #[DataProvider('invalidRegistrationData')]
    public function test_registration_rejects_invalid_input(array $input, string $field, string $message): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), array_replace([
            'nama' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ], $input));

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([$field => $message]);
        $response->assertSessionMissing('_old_input.password');
        $response->assertSessionMissing('_old_input.password_confirmation');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    /**
     * @return array<string, array{array<string, string|array<int, string>>, string, string}>
     */
    public static function invalidRegistrationData(): array
    {
        return [
            'name must be text' => [['nama' => ['nama']], 'nama', 'Nama harus berupa teks.'],
            'name exceeds limit' => [['nama' => str_repeat('a', 101)], 'nama', 'Nama maksimal 100 karakter.'],
            'invalid email' => [['email' => 'invalid-email'], 'email', 'Format email tidak valid.'],
            'email exceeds limit' => [['email' => str_repeat('a', 60).'@'.str_repeat('b', 30).'.example.com'], 'email', 'Email maksimal 100 karakter.'],
            'password must be text' => [['password' => ['password-baru']], 'password', 'Password harus berupa teks.'],
            'short password' => [['password' => 'pendek', 'password_confirmation' => 'pendek'], 'password', 'Password minimal 8 karakter.'],
            'mismatched confirmation' => [['password_confirmation' => 'password-lain'], 'password', 'Konfirmasi password tidak sama.'],
            'missing confirmation' => [['password_confirmation' => ''], 'password', 'Konfirmasi password tidak sama.'],
        ];
    }

    public function test_authenticated_user_cannot_view_registration_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_cannot_register_another_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('register.store'), [
            'nama' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseCount('users', 1);
        $this->assertAuthenticatedAs($user);
    }
}
