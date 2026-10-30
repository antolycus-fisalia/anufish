<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtikelCrudTest extends TestCase
{
    use RefreshDatabase;

    private function makeArtikel(
        User $user,
        array $attributes = []
    ): Artikel {
        $artikel = new Artikel(array_merge([
            'judul' => 'Artikel Ikan',
            'kategori' => 'Edukasi',
            'isi' => 'Informasi tentang ikan.',
        ], $attributes));

        $artikel->user_id = $user->id;
        $artikel->status = 'menunggu';
        $artikel->save();

        return $artikel;
    }

    public function test_user_can_create_article(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('artikel.store'), [
                'judul' => 'Ikan Tuna',
                'kategori' => 'Edukasi',
                'isi' => 'Artikel tentang tuna.',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('artikel', [
            'user_id' => $user->id,
            'judul' => 'Ikan Tuna',
            'status' => 'menunggu',
        ]);
    }

    public function test_user_cannot_override_owner_or_status(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('artikel.store'), [
                'judul' => 'Artikel Aman',
                'kategori' => 'Edukasi',
                'isi' => 'Contoh isi.',
                'user_id' => $other->id,
                'status' => 'disetujui',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('artikel', [
            'user_id' => $user->id,
            'judul' => 'Artikel Aman',
            'status' => 'menunggu',
        ]);
    }

    public function test_user_can_update_own_article(): void
    {
        $user = User::factory()->create();
        $artikel = $this->makeArtikel($user);

        $response = $this->actingAs($user)
            ->putJson(route('artikel.update', $artikel), [
                'judul' => 'Judul Baru',
                'kategori' => 'Edukasi',
                'isi' => 'Isi terbaru.',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'judul' => 'Judul Baru',
        ]);
    }

    public function test_edited_approved_article_returns_to_pending(): void
    {
        $user = User::factory()->create();
        $artikel = $this->makeArtikel($user);
        $artikel->status = 'disetujui';
        $artikel->save();

        $this->actingAs($user)
            ->putJson(route('artikel.update', $artikel), [
                'judul' => 'Artikel Diperbarui',
                'kategori' => 'Edukasi',
                'isi' => 'Konten diperbarui.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_user_cannot_update_another_users_article(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $artikel = $this->makeArtikel($owner);

        $this->actingAs($other)
            ->putJson(route('artikel.update', $artikel), [
                'judul' => 'Artikel Diretas',
                'kategori' => 'Edukasi',
                'isi' => 'Konten palsu.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'judul' => 'Artikel Ikan',
        ]);
    }

    public function test_user_can_delete_own_article(): void
    {
        $user = User::factory()->create();
        $artikel = $this->makeArtikel($user);

        $this->actingAs($user)
            ->deleteJson(route('artikel.destroy', $artikel))
            ->assertOk();

        $this->assertDatabaseMissing('artikel', [
            'id' => $artikel->id,
        ]);
    }

    public function test_invalid_article_does_not_enter_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('artikel.store'), [
                'judul' => '',
                'kategori' => 'Edukasi',
                'isi' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['judul', 'isi']);

        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_user_only_sees_their_own_articles(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $own = $this->makeArtikel($user);
        $foreign = $this->makeArtikel($other);

        $response = $this->actingAs($user)
            ->getJson(route('artikel.index'));

        $response->assertOk()
            ->assertJsonFragment(['id' => $own->id])
            ->assertJsonMissing(['id' => $foreign->id]);
    }

    public function test_valid_image_is_stored_with_generated_name(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('artikel.store'), [
                'judul' => 'Artikel Bergambar',
                'kategori' => 'Edukasi',
                'isi' => 'Contoh artikel.',
                'gambar' => UploadedFile::fake()->image('ikan.png'),
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertCreated();

        $path = $response->json('data.gambar');

        $this->assertNotNull($path);
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->assertNotSame('ikan.png', basename($path));
    }

    public function test_oversized_image_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('artikel.store'), [
                'judul' => 'Artikel Bergambar',
                'kategori' => 'Edukasi',
                'isi' => 'Contoh artikel.',
                'gambar' => UploadedFile::fake()
                    ->image('ikan.jpg')
                    ->size(6000),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('gambar');

        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_guest_cannot_create_article(): void
    {
        $this->postJson(route('artikel.store'), [
            'judul' => 'Artikel Tamu',
            'kategori' => 'Edukasi',
            'isi' => 'Konten artikel.',
        ])->assertUnauthorized();
    }
}
