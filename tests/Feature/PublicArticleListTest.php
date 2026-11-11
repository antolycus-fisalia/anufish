<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicArticleListTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticle(
        User $user,
        string $title,
        string $status
    ): Artikel {
        $article = new Artikel([
            'judul' => $title,
            'kategori' => 'Edukasi',
            'isi' => '<p>Konten artikel pengujian.</p>',
        ]);

        $article->user_id = $user->id;
        $article->status = $status;
        $article->save();

        return $article;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('artikel-publik.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_public_articles(): void
    {
        $user = User::factory()->create();

        $this->makeArticle($user, 'Artikel Terbit', 'disetujui');

        $this->actingAs($user)
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertSee('Artikel Terbit');
    }

    public function test_only_approved_articles_are_visible(): void
    {
        $user = User::factory()->create();

        $this->makeArticle($user, 'Artikel Disetujui', 'disetujui');
        $this->makeArticle($user, 'Artikel Menunggu', 'menunggu');
        $this->makeArticle($user, 'Artikel Ditolak', 'ditolak');

        $this->actingAs($user)
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertSee('Artikel Disetujui')
            ->assertDontSee('Artikel Menunggu')
            ->assertDontSee('Artikel Ditolak');
    }

    public function test_public_articles_show_author(): void
    {
        $author = User::factory()->create([
            'name' => 'Penulis Anufish',
        ]);

        $viewer = User::factory()->create();

        $this->makeArticle($author, 'Artikel Edukasi', 'disetujui');

        $this->actingAs($viewer)
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertSee('Penulis Anufish');
    }

    public function test_empty_state_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertSee(__('article.public.empty_title'));
    }

    public function test_public_articles_are_paginated(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $this->makeArticle(
                $user,
                "Artikel Publik {$i}",
                'disetujui'
            );
        }

        $this->actingAs($user)
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertViewHas('artikel', function ($artikel) {
                return $artikel->count() === 10
                    && $artikel->total() === 11
                    && $artikel->lastPage() === 2;
            });

        $this->actingAs($user)
            ->get(route('artikel-publik.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('artikel', function ($artikel) {
                return $artikel->count() === 1
                    && $artikel->currentPage() === 2;
            });
    }

    public function test_public_article_page_supports_english(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('artikel-publik.index'))
            ->assertOk()
            ->assertSee('No articles yet');
    }
}
