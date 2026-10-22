<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_uses_initials_when_stored_photo_is_too_small(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('tiny.png', 2, 2);
        $path = $file->store('profile-photos', 'public');
        $user = User::factory()->create(['name' => 'admin', 'profile_photo_path' => $path]);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('photoUrl', null)
            ->assertSee('AD')
            ->assertDontSee('data-profile-photo');
    }

    public function test_profile_displays_valid_stored_photo(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('avatar.png', 80, 80)->store('profile-photos', 'public');
        $user = User::factory()->create(['profile_photo_path' => $path]);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('photoUrl', Storage::disk('public')->url($path))
            ->assertSee('data-profile-photo');
    }

    public function test_tiny_profile_photo_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('tiny.png', 2, 2),
        ])->assertSessionHasErrors('profile_photo');
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@test.com',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@test.com',
        ]);
    }

    public function test_user_cannot_update_other_user_profile(): void
    {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->assertTrue(
        $user->can('update', $user)
    );

    $this->assertFalse(
        $user->can('update', $otherUser)
    );
    }

    public function test_invalid_data_does_not_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => '',
                'email' => 'bukan-email',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Lama',
        ]);
    }

    public function test_profile_photo_must_not_exceed_5_mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()
            ->image('profile.jpg', 80, 80)
            ->size(6000);

        $response = $this
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $file,
            ]);

        $response->assertSessionHasErrors('profile_photo');
    }

    public function test_valid_profile_photo_is_stored(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()
            ->image('profile.jpg', 80, 80)
            ->size(1000);

        $this
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $file,
            ]);

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);

        $this->assertTrue(
        Storage::disk('public')->exists($user->profile_photo_path)
        );
    }
}
