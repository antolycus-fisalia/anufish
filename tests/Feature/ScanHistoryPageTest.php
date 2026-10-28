<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanHistoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_scan_history(): void
    {
        $this
            ->get('/scan/history')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_empty_scan_history(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get('/scan/history')
            ->assertOk()
            ->assertSee('Riwayat Scan')
            ->assertSee('Belum ada riwayat scan');
    }

    public function test_scan_history_renders_in_indonesian(): void
    {
        $user = User::factory()->create();

        $this
            ->withSession(['locale' => 'id'])
            ->actingAs($user)
            ->get('/scan/history')
            ->assertOk()
            ->assertSee('Riwayat Scan')
            ->assertSee('Belum ada riwayat scan')
            ->assertSee('Mulai Scan');
    }

    public function test_scan_history_renders_in_english(): void
    {
        $user = User::factory()->create();

        $this
            ->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get('/scan/history')
            ->assertOk()
            ->assertSee('Scan History')
            ->assertSee('No scan history yet')
            ->assertSee('Start Scan');
    }
}
