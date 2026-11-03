<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminUserEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    private function regularUser(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_user_edit_data(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.users.edit', $target));

        $response->assertOk()
            ->assertJsonPath('data.id', $target->id)
            ->assertJsonPath('data.email', $target->email)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
            ])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
        ]);
    }

    public function test_admin_can_keep_existing_email(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Nama Diperbarui',
                'email' => $target->email,
            ])
            ->assertOk();
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();
        $other = $this->regularUser();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Nama Baru',
                'email' => $other->email,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'email' => $target->email,
        ]);
    }

    public function test_non_admin_cannot_edit_user(): void
    {
        $user = $this->regularUser();
        $target = $this->regularUser();

        $this->actingAs($user)
            ->getJson(route('admin.users.edit', $target))
            ->assertForbidden();

        $this->actingAs($user)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Tidak Sah',
                'email' => 'tidaksah@example.com',
            ])
            ->assertForbidden();
    }

    public function test_guest_cannot_update_user(): void
    {
        $target = $this->regularUser();

        $this->putJson(route('admin.users.update', $target), [
            'name' => 'Guest',
            'email' => 'guest@example.com',
        ])->assertUnauthorized();
    }

    public function test_missing_user_returns_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', 999999999), [
                'name' => 'Tidak Ada',
                'email' => 'missing@example.com',
            ])
            ->assertNotFound();
    }

    public function test_admin_cannot_modify_role_status_or_password(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        $originalPassword = $target->password;

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Nama Aman',
                'email' => 'aman@example.com',
                'role' => 'admin',
                'status' => 'diblokir',
                'password' => 'password-baru',
            ])
            ->assertOk();

        $target->refresh();

        $this->assertSame('user', $target->role);
        $this->assertSame('aktif', $target->status);
        $this->assertSame(
            $originalPassword,
            $target->password
        );
    }

    public function test_admin_update_records_safe_audit_log(): void
    {
        Log::spy();

        $admin = $this->admin();
        $target = $this->regularUser();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Nama Audit',
                'email' => 'audit@example.com',
            ])
            ->assertOk();

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(
                function ($message, $context) use (
                    $admin,
                    $target
                ) {
                    return $message === 'admin.user.updated'
                        && $context['actor_id'] === $admin->id
                        && $context['target_id'] === $target->id
                        && $context['changed_fields'] === [
                            'name',
                            'email',
                        ]
                        && ! array_key_exists('password', $context)
                        && ! array_key_exists('email', $context);
                }
            );
    }

    public function test_database_save_failure_does_not_return_success(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        User::saving(function (User $user) use ($target) {
            if ($user->id === $target->id) {
                return false;
            }
        });

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => 'Perubahan Gagal',
                'email' => 'gagal@example.com',
            ])
            ->assertInternalServerError();

        $this->assertDatabaseMissing('users', [
            'id' => $target->id,
            'email' => 'gagal@example.com',
        ]);
    }

    public function test_validation_message_in_indonesian(): void
    {
        app()->setLocale('id');

        $admin = $this->admin();
        $target = $this->regularUser();

        $this->actingAs($admin)
            ->putJson(route('admin.users.update', $target), [
                'name' => '',
                'email' => $target->email,
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.name.0',
                'Nama pengguna wajib diisi.'
            );
    }

    public function test_validation_message_in_english(): void
    {
        $admin = $this->admin();
        $target = $this->regularUser();

        $this->actingAs($admin)
            ->withSession(['locale' => 'en'])
            ->putJson(route('admin.users.update', $target), [
                'name' => '',
                'email' => $target->email,
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.name.0',
                'User name is required.'
            );
    }
}
