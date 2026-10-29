<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_retry_messages_are_translated_in_both_locales(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['id' => 'Coba ambil referensi lagi', 'en' => 'Retry fetching the reference'] as $locale => $retry) {
            $this->withSession(['locale' => $locale])->get('/scan')
                ->assertOk()->assertSee($retry)->assertDontSee('scan.reference.retry');
        }
        $this->assertSame(array_keys(require lang_path('id/scan.php')),
            array_keys(require lang_path('en/scan.php')));
        $this->assertSame(array_keys((require lang_path('id/scan.php'))['reference']),
            array_keys((require lang_path('en/scan.php'))['reference']));
    }

    public function test_scan_page_renders_in_indonesian(): void
    {
        $user = User::factory()->create();

        $this
            ->withSession(['locale' => 'id'])
            ->actingAs($user)
            ->get('/scan')
            ->assertOk()
            ->assertSee(
                'data-error-request="Identifikasi gagal. Silakan coba lagi."',
                false
            )
            ->assertSee(
                'data-error-result="Hasil identifikasi belum tersedia."',
                false
            );
    }

    public function test_scan_page_renders_in_english(): void
    {
        $user = User::factory()->create();

        $this
            ->withSession(['locale' => 'en'])
            ->actingAs($user)
            ->get('/scan')
            ->assertOk()
            ->assertSee(
                'data-error-request="Identification failed. Please try again."',
                false
            )
            ->assertSee(
                'data-error-result="Identification result is not available yet."',
                false
            );
    }
}
