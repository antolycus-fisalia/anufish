<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->nama);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'nama' => 'Nama Baru',
                'email' => 'baru@example.com',
            ]);

        $response->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nama' => 'Nama Baru',
            'email' => 'baru@example.com',
        ]);
    }

    public function test_email_must_be_unique(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/profile', [
                'nama' => 'Nama Baru',
                'email' => $otherUser->email,
            ]);

        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    
}
