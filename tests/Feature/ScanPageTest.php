<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_loading_page_requires_login_and_has_localized_status(): void
    {
        $this->get('/scan/detail-loading')->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        foreach (['id' => 'Menyiapkan detail ikan...', 'en' => 'Preparing fish details...'] as $locale => $title) {
            $this->withSession(['locale' => $locale])->get('/scan/detail-loading')
                ->assertOk()->assertSee($title)->assertSee('role="status"', false)
                ->assertSee('motion-reduce:animate-none', false);
        }
    }

    public function test_reference_retry_messages_are_translated_in_both_locales(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['id' => 'Coba ambil referensi lagi', 'en' => 'Retry fetching the reference'] as $locale => $retry) {
            $this->withSession(['locale' => $locale])->get('/scan')
                ->assertOk()->assertSee($retry)->assertDontSee('scan.reference.retry')
                ->assertSee('data-detail-loading-url="'.route('scan.detail-loading').'"', false)
                ->assertSee('data-fish-detail-url="'.route('fish.show', ['taxonId' => '__TAXON_ID__']).'"', false)
                ->assertDontSee('x-show="candidates.length > 1"', false);
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
