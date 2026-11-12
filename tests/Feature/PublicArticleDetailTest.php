<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicArticleDetailTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticle(
        User $user,
        string $status = 'disetujui',
        array $attributes = []
    ): Artikel {
        $article = new Artikel([
            'judul' => $attributes['judul'] ?? 'Artikel Edukasi Ikan',
            'kategori' => $attributes['kategori'] ?? 'Edukasi',
            'isi' => $attributes['isi'] ?? '<p>Informasi tentang ikan.</p>',
            'gambar' => $attributes['gambar'] ?? null,
        ]);

        $article->user_id = $user->id;
        $article->status = $status;
        $article->save();

        return $article;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $article = $this->makeArticle($user);

        $this->get(route('artikel-publik.show', $article->id))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_read_approved_article(): void
    {
        $author = User::factory()->create([
            'name' => 'Penulis Anufish',
        ]);

        $viewer = User::factory()->create();

        $article = $this->makeArticle($author);

        $this->actingAs($viewer)
            ->get(route('artikel-publik.show', $article->id))
            ->assertOk()
            ->assertSee('Artikel Edukasi Ikan')
            ->assertSee('Penulis Anufish')
            ->assertSee('Informasi tentang ikan.');
    }

    public function test_pending_article_cannot_be_accessed(): void
    {
        $user = User::factory()->create();
        $article = $this->makeArticle($user, 'menunggu');

        $this->actingAs($user)
            ->get(route('artikel-publik.show', $article->id))
            ->assertNotFound();
    }

    public function test_rejected_article_cannot_be_accessed(): void
    {
        $user = User::factory()->create();
        $article = $this->makeArticle($user, 'ditolak');

        $this->actingAs($user)
            ->get(route('artikel-publik.show', $article->id))
            ->assertNotFound();
    }

    public function test_missing_article_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('artikel-publik.show', 999999))
            ->assertNotFound();
    }

    public function test_article_content_is_sanitized(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $article = $this->makeArticle($author, 'disetujui', [
            'isi' => '<p>Konten aman</p><script>alert("xss")</script>',
        ]);

        $this->actingAs($viewer)
            ->get(route('artikel-publik.show', $article->id))
            ->assertOk()
            ->assertSee('Konten aman')
            ->assertDontSee('<script>', false)
            ->assertDontSee('alert("xss")');
    }

    public function test_approved_article_detail_supports_english(): void
    {
        $user = User::factory()->create();
        $article = $this->makeArticle($user);

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('artikel-publik.show', $article->id))
            ->assertOk()
            ->assertSee('Back to articles');
    }

    public function test_invalid_image_path_is_not_rendered(): void
    {
        $user = User::factory()->create();

        $article = $this->makeArticle($user, 'disetujui', [
            'gambar' => '../private/secret.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('artikel-publik.show', $article->id))
            ->assertOk()
            ->assertDontSee('private/secret.jpg');
    }
}
