<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin.name' => 'Admin QA', 'admin.email' => 'admin@example.test', 'admin.password' => 'testpassword']);
    }

    public function test_database_seed_creates_an_active_admin_that_can_login(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertTrue(Hash::check('testpassword', $admin->password));
        $this->post('/login', ['email' => $admin->email, 'password' => 'testpassword'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_reseeding_preserves_existing_password_name_and_blocked_status(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test', 'name' => 'Existing Admin',
            'password' => 'existingpassword', 'role' => 'admin', 'status' => 'diblokir',
        ]);
        $hash = $admin->password;
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);
        $admin->refresh();
        $this->assertSame(1, User::count());
        $this->assertSame($hash, $admin->password);
        $this->assertSame('Existing Admin', $admin->name);
        $this->assertFalse($admin->isActive());
    }

    public function test_seed_does_not_promote_a_regular_user_with_the_same_email(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test', 'role' => 'user']);
        try {
            $this->seed(AdminSeeder::class);
            $this->fail('Expected an email conflict.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('non-admin', $exception->getMessage());
        }
        $this->assertFalse($user->fresh()->isAdmin());
        $this->assertSame(1, User::count());
    }

    public function test_missing_password_cannot_create_an_admin(): void
    {
        config(['admin.password' => null]);
        try {
            $this->seed(AdminSeeder::class);
            $this->fail('Expected validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }
        $this->assertSame(0, User::count());
    }
}
