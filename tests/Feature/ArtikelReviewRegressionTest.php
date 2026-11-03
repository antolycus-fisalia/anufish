<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ArtikelReviewRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['judul' => 'Tuna', 'kategori' => 'Edukasi', 'isi' => 'Fish information.'];
    }

    public function test_success_and_validation_messages_follow_session_locale(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['id', 'en'] as $locale) {
            $messages = (require lang_path($locale.'/article.php'));
            $this->withSession(['locale' => $locale]);
            $id = $this->postJson('/artikel', $this->data())->assertCreated()->assertJsonPath('message', $messages['messages']['created'])->json('data.id');
            $this->putJson('/artikel/'.$id, $this->data())->assertOk()->assertJsonPath('message', $messages['messages']['updated']);
            $this->postJson('/artikel', array_replace($this->data(), ['judul' => '']))->assertUnprocessable()->assertJsonPath('errors.judul.0', $messages['validation']['title_required']);
            $this->deleteJson('/artikel/'.$id)->assertOk()->assertJsonPath('message', $messages['messages']['deleted']);
        }
    }

    public function test_empty_markup_is_rejected_on_create_and_update_without_db_change(): void
    {
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/artikel', $this->data())->assertCreated()->json('data.id');
        $this->postJson('/artikel', array_replace($this->data(), ['isi' => '<p></p>']))->assertUnprocessable();
        $this->putJson('/artikel/'.$id, array_replace($this->data(), ['isi' => '<p></p>']))->assertUnprocessable();
        $this->assertDatabaseCount('artikel', 1);
        $this->assertDatabaseHas('artikel', ['id' => $id, 'isi' => 'Fish information.']);
    }

    public function test_save_false_returns_localized_failure_not_success(): void
    {
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/artikel', $this->data())->assertCreated()->json('data.id');
        Artikel::saving(fn () => false);
        Artikel::deleting(fn () => false);
        foreach (['id', 'en'] as $locale) {
            $messages = (require lang_path($locale.'/article.php'))['messages'];
            $this->withSession(['locale' => $locale]);
            $this->postJson('/artikel', $this->data())->assertStatus(500)->assertJsonPath('message', $messages['create_failed']);
            $this->putJson('/artikel/'.$id, array_replace($this->data(), ['judul' => 'Changed']))->assertStatus(500)->assertJsonPath('message', $messages['update_failed']);
            $this->deleteJson('/artikel/'.$id)->assertStatus(500)->assertJsonPath('message', $messages['delete_failed']);
        }
        $this->assertDatabaseCount('artikel', 1);
        $this->assertDatabaseHas('artikel', ['id' => $id, 'judul' => 'Tuna']);
    }

    public function test_cleanup_exception_does_not_hide_db_failure_response(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['locale' => 'en']);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn('artikel-images/new.png');
        $disk->shouldReceive('delete')->once()->with('artikel-images/new.png')->andThrow(new RuntimeException('Cleanup failed'));
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        Artikel::creating(fn () => throw new RuntimeException('DB failed'));
        $this->postJson('/artikel', array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('fish.png')]))->assertStatus(500)->assertJsonPath('message', 'Failed to create article. Please retry.');
        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_failed_update_keeps_old_file_and_cleans_only_new_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/artikel', array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('old.png')]))->assertCreated()->json('data.id');
        $old = Artikel::findOrFail($id)->gambar;
        Artikel::updating(fn () => throw new RuntimeException('DB failed'));
        $this->putJson('/artikel/'.$id, array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('new.png')]))->assertStatus(500);
        $this->assertSame($old, Artikel::findOrFail($id)->gambar);
        Storage::disk('public')->assertExists($old);
        $this->assertSame([$old], Storage::disk('public')->allFiles('artikel-images'));
    }
}
