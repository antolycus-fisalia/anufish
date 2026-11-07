<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserArticleListTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticle(
        User $user,
        array $attributes = []
    ): Artikel {
        $article = new Artikel([
            'judul' => $attributes['judul'] ?? 'Artikel Ikan',
            'kategori' => $attributes['kategori'] ?? 'Edukasi',
            'isi' => $attributes['isi'] ?? 'Isi artikel.',
        ]);

        $article->user_id = $user->id;
        $article->status = $attributes['status'] ?? 'menunggu';
        $article->save();

        return $article;
    }

    public function test_user_only_sees_own_articles(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = $this->makeArticle($user, [
            'judul' => 'Artikel Saya',
        ]);

        $foreign = $this->makeArticle($other, [
            'judul' => 'Artikel User Lain',
        ]);

        $this->actingAs($user)
            ->getJson(route('artikel.index'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $own->id,
                'judul' => 'Artikel Saya',
            ])
            ->assertJsonMissing([
                'id' => $foreign->id,
                'judul' => 'Artikel User Lain',
            ]);
    }

    public function test_user_cannot_delete_another_users_article(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $article = $this->makeArticle($owner);

        $this->actingAs($other)
            ->deleteJson(route('artikel.destroy', $article))
            ->assertForbidden();

        $this->assertDatabaseHas('artikel', [
            'id' => $article->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_article_list_is_paginated(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 11; $i++) {
            $this->makeArticle($user, [
                'judul' => "Artikel {$i}",
                'status' => ['menunggu', 'disetujui', 'ditolak'][($i - 1) % 3],
            ]);
        }

        $this->actingAs($user)
            ->getJson(route('artikel.index'))
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 11);
    }

    public function test_empty_article_list_returns_empty_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('artikel.index'))
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('total', 0);
    }
}
