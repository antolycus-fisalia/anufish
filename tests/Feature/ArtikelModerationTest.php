<?php

namespace Tests\Feature;

use App\Events\ArtikelModerated;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ArtikelModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function makeUser(): User
    {
        return User::factory()->create(['role' => 'user', 'status' => 'aktif']);
    }

    /**
     * status & user_id tidak masuk $fillable, ditetapkan langsung di model.
     */
    private function makeArtikel(array $attributes = []): Artikel
    {
        $status = $attributes['status'] ?? 'menunggu';
        unset($attributes['status']);

        $artikel = new Artikel(array_merge([
            'judul' => 'Ikan Cupang',
            'kategori' => 'Berita',
            'isi' => '<p>Isi artikel cupang.</p>',
        ], $attributes));
        $artikel->user_id = $attributes['user_id'] ?? $this->makeUser()->id;
        $artikel->status = $status;
        $artikel->save();

        return $artikel;
    }

    public function test_admin_can_approve_and_status_becomes_disetujui(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel();

        $this->actingAs($admin)
            ->post(route('admin.artikel.approve', $artikel))
            ->assertOk()
            ->assertJsonPath('data.status', 'disetujui');

        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'status' => 'disetujui']);
        Event::assertDispatched(ArtikelModerated::class);
    }

    public function test_reject_marks_ditolak_and_keeps_the_row(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel();

        $this->actingAs($admin)
            ->post(route('admin.artikel.reject', $artikel))
            ->assertOk()
            ->assertJsonPath('data.status', 'ditolak');

        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'status' => 'ditolak']);
        $this->assertNotSoftDeleted($artikel);
    }

    public function test_delete_is_soft_delete_and_keeps_status(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel(['status' => 'disetujui']);

        $this->actingAs($admin)
            ->post(route('admin.artikel.delete', $artikel))
            ->assertOk();

        $this->assertSoftDeleted('artikel', ['id' => $artikel->id]);
        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'status' => 'disetujui']);
    }

    public function test_non_admin_cannot_moderate(): void
    {
        Event::fake();
        $user = $this->makeUser();
        $artikel = $this->makeArtikel();

        $this->actingAs($user)->post(route('admin.artikel.approve', $artikel))->assertForbidden();
        $this->actingAs($user)->post(route('admin.artikel.reject', $artikel))->assertForbidden();
        $this->actingAs($user)->post(route('admin.artikel.delete', $artikel))->assertForbidden();

        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'status' => 'menunggu']);
    }

    public function test_second_moderation_on_the_same_status_conflicts(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel();

        $this->actingAs($admin)->post(route('admin.artikel.approve', $artikel))->assertOk();

        $stale = Artikel::findOrFail($artikel->id);

        $this->actingAs($admin)
            ->post(route('admin.artikel.approve', $stale))
            ->assertStatus(409)
            ->assertJsonPath('message', __('article.moderation.conflict'));

        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'status' => 'disetujui']);
    }

    public function test_db_failure_sends_no_notification_event(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel();

        DB::shouldReceive('transaction')->once()->andThrow(new RuntimeException('boom'));

        $this->actingAs($admin)
            ->post(route('admin.artikel.approve', $artikel))
            ->assertStatus(500)
            ->assertJsonPath('message', __('article.moderation.failed'));

        Event::assertNotDispatched(ArtikelModerated::class);
    }

    public function test_audit_log_contains_only_ids_and_no_article_content(): void
    {
        Event::fake();
        $admin = $this->makeAdmin();
        $artikel = $this->makeArtikel();
        $adminId = $admin->id;

        Log::spy();

        $this->actingAs($admin)->post(route('admin.artikel.approve', $artikel))->assertOk();

        Log::shouldHaveReceived('info')
            ->with('article.moderated', Mockery::on(function (array $context) use ($adminId, $artikel) {
                return count($context) === 3
                    && $context['action'] === 'approve'
                    && $context['actor_id'] === $adminId
                    && $context['artikel_id'] === $artikel->getKey();
            }))->once();
    }
}
