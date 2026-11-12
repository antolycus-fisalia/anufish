<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminArtikelListTest extends TestCase
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

    private function createArticle(
        User $author,
        string $title,
        string $status = 'menunggu'
    ): Artikel {
        $article = new Artikel([
            'judul' => $title,
            'kategori' => 'Ikan',
            'isi' => '<p>Konten pengujian</p>',
        ]);

        $article->user()->associate($author);
        $article->status = $status;
        $article->save();

        return $article;
    }

    public function test_guest_cannot_access_moderation_list(): void
    {
        $this->get(route('admin.artikel.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_moderation_list(): void
    {
        $this->actingAs($this->regularUser())
            ->get(route('admin.artikel.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_pending_articles(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $pending = $this->createArticle(
            $author,
            'Artikel Menunggu',
            'menunggu'
        );

        $approved = $this->createArticle(
            $author,
            'Artikel Disetujui',
            'disetujui'
        );

        $this->actingAs($admin)
            ->get(route('admin.artikel.index'))
            ->assertOk()
            ->assertSee($pending->judul)
            ->assertDontSee($approved->judul);
    }

    public function test_admin_can_filter_approved_articles(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $article = $this->createArticle(
            $author,
            'Artikel Disetujui',
            'disetujui'
        );

        $this->actingAs($admin)
            ->get(route('admin.artikel.index', [
                'status' => 'disetujui',
            ]))
            ->assertOk()
            ->assertSee($article->judul);
    }

    public function test_admin_can_view_all_statuses(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $pending = $this->createArticle(
            $author,
            'Artikel Menunggu',
            'menunggu'
        );

        $approved = $this->createArticle(
            $author,
            'Artikel Disetujui',
            'disetujui'
        );

        $this->actingAs($admin)
            ->get(route('admin.artikel.index', [
                'status' => '',
            ]))
            ->assertOk()
            ->assertSee($pending->judul)
            ->assertSee($approved->judul);
    }

    public function test_admin_can_view_article_detail(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $article = $this->createArticle(
            $author,
            'Detail Artikel'
        );

        $this->actingAs($admin)
            ->get(route('admin.artikel.show', $article))
            ->assertOk()
            ->assertSee($article->judul)
            ->assertSee($author->name);
    }

    public function test_regular_user_cannot_view_article_detail(): void
    {
        $author = $this->regularUser();

        $article = $this->createArticle(
            $author,
            'Artikel Rahasia'
        );

        $this->actingAs($author)
            ->get(route('admin.artikel.show', $article))
            ->assertForbidden();
    }

    public function test_empty_state_is_displayed(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.artikel.index'))
            ->assertOk()
            ->assertSee(
                __('article.admin_moderation.empty')
            );
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.artikel.index', [
                'status' => 'invalid',
            ]))
            ->assertSessionHasErrors('status');
    }

    public function test_moderation_list_is_paginated(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $first = $this->createArticle(
            $author,
            'Artikel Pertama Khusus'
        );

        for ($i = 1; $i <= 10; $i++) {
            $this->createArticle(
                $author,
                "Artikel Pengujian {$i}"
            );
        }

        $last = $this->createArticle(
            $author,
            'Artikel Terakhir Khusus'
        );

        $this->actingAs($admin)
            ->get(route('admin.artikel.index'))
            ->assertOk()
            ->assertSee($last->judul)
            ->assertDontSee($first->judul);

        $this->get(route('admin.artikel.index', [
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee($first->judul)
            ->assertDontSee($last->judul);
    }

    public function test_soft_deleted_article_is_not_listed(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $article = $this->createArticle(
            $author,
            'Artikel Sudah Dihapus'
        );

        $article->delete();

        $this->actingAs($admin)
            ->get(route('admin.artikel.index', [
                'status' => '',
            ]))
            ->assertOk()
            ->assertDontSee($article->judul);

        $this->get(route('admin.artikel.show', $article))
            ->assertNotFound();
    }

    public function test_legacy_article_content_is_sanitized(): void
    {
        $admin = $this->admin();
        $author = $this->regularUser();

        $article = $this->createArticle(
            $author,
            'Artikel Pengujian XSS'
        );

        // Simulasikan konten lama yang belum disanitasi.
        DB::table('artikel')
            ->where('id', $article->id)
            ->update([
                'isi' => '<p>Konten aman</p><script>alert("xss")</script>',
            ]);

        $this->actingAs($admin)
            ->get(route('admin.artikel.show', $article))
            ->assertOk()
            ->assertSee('<p>Konten aman</p>', false)
            ->assertDontSee(
                '<script>alert("xss")</script>',
                false
            )
            ->assertDontSee('alert("xss")', false);
    }
}
