<?php

namespace Tests\Feature;

use App\Events\ArtikelModerated;
use App\Listeners\SendArtikelModerationNotification;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ArtikelModerationNotificationTest extends TestCase
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
    private function makeArtikel(User $owner, array $attributes = []): Artikel
    {
        $status = $attributes['status'] ?? 'menunggu';
        unset($attributes['status']);

        $artikel = new Artikel(array_merge([
            'judul' => 'Ikan Cupang',
            'kategori' => 'Berita',
            'isi' => '<p>Isi artikel cupang.</p>',
        ], $attributes));
        $artikel->user_id = $owner->id;
        $artikel->status = $status;
        $artikel->save();

        return $artikel;
    }

    private function moderate(
        User $admin,
        string $action,
        Artikel $artikel,
        int $revision,
        int $expectedStatus = 200
    ): void {
        $this->actingAs($admin)
            ->postJson(route("admin.artikel.{$action}", $artikel), [
                'moderation_revision' => $revision,
            ])
            ->assertStatus($expectedStatus);
    }

    private function notificationIndex(User $user)
    {
        return $this->actingAs($user)
            ->getJson(route('artikel-saya.notifikasi.index'));
    }

    public function test_approve_notifies_owner_with_latest_status(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'notifiable_type' => User::class,
        ]);

        $this->notificationIndex($owner)
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.action', 'approve')
            ->assertJsonPath('data.0.status', 'disetujui')
            ->assertJsonPath('data.0.judul', 'Ikan Cupang')
            ->assertJsonPath('data.0.read', false);
    }

    public function test_reject_notifies_owner_with_latest_status(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'reject', $artikel, $artikel->moderation_revision);

        $this->assertDatabaseCount('notifications', 1);

        $this->notificationIndex($owner)
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.action', 'reject')
            ->assertJsonPath('data.0.status', 'ditolak');
    }

    public function test_delete_notifies_owner(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'delete', $artikel, $artikel->moderation_revision);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSoftDeleted('artikel', ['id' => $artikel->id]);

        $this->notificationIndex($owner)
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.action', 'delete');
    }

    public function test_only_the_owner_receives_and_reads_the_notification(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        $notificationId = $owner->notifications->first()->id;

        // User lain tidak melihat notifikasi milik owner.
        $this->notificationIndex($other)
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('unread_count', 0);

        // User lain tidak dapat menandai notifikasi owner sebagai dibaca.
        $this->actingAs($other)
            ->postJson(route('artikel-saya.notifikasi.baca', ['notificationId' => $notificationId]))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);

        // Owner menandai notifikasinya sebagai dibaca.
        $this->actingAs($owner)
            ->postJson(route('artikel-saya.notifikasi.baca', ['notificationId' => $notificationId]))
            ->assertOk()
            ->assertJsonPath('data.read', true);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);

        $this->notificationIndex($owner)
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
    }

    public function test_retrying_the_same_action_does_not_duplicate_notifications(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);
        $revision = $artikel->moderation_revision;

        $this->moderate($admin, 'approve', $artikel, $revision);

        // Ulangi aksi yang sama dengan revision lama: konflik, tanpa notifikasi baru.
        $this->moderate($admin, 'approve', $artikel, $revision, 409);
        $this->moderate($admin, 'reject', $artikel, $revision, 409);

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_each_transition_sends_exactly_one_notification(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        // Owner mengedit artikel: kembali ke menunggu, revision bertambah.
        $this->actingAs($owner)
            ->putJson(route('artikel.update', $artikel), [
                'judul' => 'Ikan Cupang Baru',
                'kategori' => 'Berita',
                'isi' => '<p>Isi baru.</p>',
            ])
            ->assertOk();

        $artikel = $artikel->fresh();

        $this->assertSame('menunggu', $artikel->status);
        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_deleted_article_notification_is_kept_without_a_link(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        // Owner menghapus artikelnya: notifikasi tetap ada, tautan hilang.
        $this->actingAs($owner)
            ->deleteJson(route('artikel.destroy', $artikel))
            ->assertOk();

        $this->notificationIndex($owner)
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.action', 'approve')
            ->assertJsonPath('data.0.link', null);
    }

    public function test_failed_delivery_is_logged_without_aborting_moderation(): void
    {
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->once()->with(
            'article.notification.delivery_failed',
            \Mockery::on(function (array $context): bool {
                return $context['action'] === 'approve'
                    && $context['artikel_id'] === 999999;
            })
        );

        $admin = $this->makeAdmin();
        $owner = $this->makeUser();
        $artikel = $this->makeArtikel($owner);

        // Event dengan artikel yang tidak ada: listener mencatat gagal, tidak melempar.
        app(Dispatcher::class)->dispatch(
            new ArtikelModerated('approve', $admin->id, 999999)
        );

        $this->assertDatabaseCount('notifications', 0);

        // Moderasi berikutnya tetap sukses dan mengirim notifikasi normal.
        $this->moderate($admin, 'approve', $artikel, $artikel->moderation_revision);

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_listener_returns_without_throwing_when_owner_is_missing(): void
    {
        Log::shouldReceive('warning')->once();

        $listener = new SendArtikelModerationNotification;

        $listener->handle(new ArtikelModerated('approve', 1, 999999));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_notifications_page_renders_in_both_locales(): void
    {
        $owner = $this->makeUser();

        $this->actingAs($owner)
            ->get(route('artikel-saya.index'))
            ->assertOk()
            ->assertSee('Notifikasi Moderasi')
            ->assertSee('data-notifications', false);

        $this->withSession(['locale' => 'en'])
            ->actingAs($owner)
            ->get(route('artikel-saya.index'))
            ->assertOk()
            ->assertSee('Moderation Notifications');
    }

    public function test_notification_translation_keys_match_both_locales(): void
    {
        $id = require lang_path('id/article.php');
        $en = require lang_path('en/article.php');

        $this->assertSame(
            array_keys($id['notification']),
            array_keys($en['notification']),
        );

        $this->assertStringContainsString(
            ':title',
            $id['notification']['message_approved'],
        );
        $this->assertStringContainsString(
            ':title',
            $en['notification']['message_approved'],
        );
    }
}
