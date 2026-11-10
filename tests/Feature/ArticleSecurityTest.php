<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use App\Services\ArticleHtmlSanitizer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ArticleSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function data(string $html = '<p>Informasi <strong>ikan</strong>.</p>'): array
    {
        return ['judul' => 'Ikan', 'kategori' => 'Edukasi', 'isi' => $html];
    }

    private function artikel(User $owner): Artikel
    {
        $artikel = new Artikel($this->data());
        $artikel->user_id = $owner->id;
        $artikel->status = 'menunggu';
        $artikel->save();

        return $artikel;
    }

    public function test_policy_keeps_owner_crud_and_admin_moderation_separate(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $artikel = $this->artikel($owner);
        $this->actingAs($other)->getJson('/artikel/'.$artikel->id)->assertForbidden();
        foreach ([$other, $admin] as $actor) {
            $this->actingAs($actor)->putJson('/artikel/'.$artikel->id, $this->data())->assertForbidden();
            $this->deleteJson('/artikel/'.$artikel->id)->assertForbidden();
        }
        $this->actingAs($admin)->getJson('/artikel/'.$artikel->id)->assertOk();
        $this->assertTrue(Gate::forUser($admin)->allows('moderate', $artikel));
        $this->assertFalse(Gate::forUser($owner)->allows('moderate', $artikel));
        $this->assertTrue(Gate::forUser($owner)->allows('update', $artikel));
        $this->assertDatabaseHas('artikel', ['id' => $artikel->id, 'user_id' => $owner->id]);
    }

    public function test_unauthorized_update_is_rejected_before_validation_or_upload(): void
    {
        Storage::fake('public');
        $artikel = $this->artikel(User::factory()->create());
        $this->actingAs(User::factory()->create())->putJson('/artikel/'.$artikel->id, ['judul' => '', 'gambar' => UploadedFile::fake()->image('attack.png')])->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_blocked_owner_cannot_modify_article_in_either_locale(): void
    {
        $owner = User::factory()->create(['status' => 'diblokir']);
        $artikel = $this->artikel($owner);
        $this->actingAs($owner);
        foreach (['id', 'en'] as $locale) {
            $message = (require lang_path($locale.'/article.php'))['messages']['forbidden'];
            $this->withSession(['locale' => $locale])->postJson('/artikel', $this->data())->assertForbidden()->assertJsonPath('message', $message);
            $this->putJson('/artikel/'.$artikel->id, $this->data())->assertForbidden();
        }
    }

    public function test_allowed_formatting_is_preserved_and_dangerous_markup_is_removed_on_write(): void
    {
        $this->actingAs(User::factory()->create());
        $html = '<h2>Judul</h2><p><strong>Ikan</strong><em> laut</em></p><script>alert(1)</script><img src="https://example.test/fish.jpg" onerror="alert(2)"><a href="javascript:alert(3)" onclick="alert(4)">Tautan</a><iframe src="https://example.test"></iframe>';
        $id = $this->postJson('/artikel', $this->data($html))->assertCreated()->json('data.id');
        $stored = DB::table('artikel')->where('id', $id)->value('isi');
        $this->assertStringContainsString('<strong>Ikan</strong>', $stored);
        $this->assertStringContainsString('https://example.test/fish.jpg', $stored);
        foreach (['script', 'onerror', 'onclick', 'javascript:', 'iframe', 'alert('] as $bad) {
            $this->assertStringNotContainsString($bad, $stored);
        }
        $this->putJson('/artikel/'.$id, $this->data('<p>Update</p><svg onload="alert(5)"></svg>'))->assertOk();
        $this->assertStringNotContainsString('svg', DB::table('artikel')->where('id', $id)->value('isi'));
    }

    public function test_url_scheme_obfuscation_and_embedded_active_content_are_dropped(): void
    {
        $sanitizer = app(ArticleHtmlSanitizer::class);
        foreach ([
            '<a href="jav&#x61;script:alert(1)">Ikan</a>',
            "<a href=\"java\tscript:alert(1)\">Ikan</a>",
            '<img src="data:image/svg+xml;base64,PHN2Zz4=">',
            '<a href="//example.test/">Ikan</a>',
            '<a href="/relative">Ikan</a>',
            '<img src="file:///secret">',
            '<math><mtext><img src=x onerror=alert(1)></mtext></math>',
            '<svg><foreignObject><script>alert(1)</script></foreignObject></svg>',
            '<p style="position:fixed" id="login" class="overlay" onclick="alert(1)">Ikan</p>',
        ] as $input) {
            $safe = $sanitizer->sanitize($input);
            foreach (['javascript:', 'data:', 'file:', 'href=', 'src=', 'script', 'svg', 'math', 'onerror', 'onclick', 'style=', 'id=', 'class='] as $bad) {
                $this->assertStringNotContainsString($bad, $safe);
            }
        }
        $safe = $sanitizer->sanitize('<a href="https://example.test/fish">Ikan</a>');
        $this->assertStringContainsString('href="https://example.test/fish"', $safe);
        $this->assertStringContainsString('rel="noopener noreferrer"', $safe);
        $this->assertSame($safe, $sanitizer->sanitize($safe));
    }

    public function test_empty_after_sanitization_is_rejected_without_writing(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['<script>alert(1)</script>', '<p>&nbsp; &#8203;</p>', '<img src="https://example.test/fish.jpg">'] as $input) {
            $this->postJson('/artikel', $this->data($input))->assertUnprocessable()->assertJsonValidationErrors('isi');
        }
        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_legacy_raw_payload_is_safe_when_read_and_rendered_by_public_content_component(): void
    {
        $owner = User::factory()->create();
        $artikel = $this->artikel($owner);
        $unsafe = '<p>Fish</p><script>alert(1)</script><img src=x onerror=alert(2)>';
        DB::table('artikel')->where('id', $artikel->id)->update(['isi' => $unsafe, 'status' => 'disetujui']);
        $this->actingAs($owner)->getJson('/artikel/'.$artikel->id)->assertOk()->assertJsonMissing(['isi' => $unsafe]);
        $model = $artikel->fresh();
        $this->assertStringNotContainsString('onerror', $model->toArray()['isi']);
        $rendered = Blade::render('<x-article.content :html="$html" />', ['html' => $unsafe]);
        $this->assertStringContainsString('<p>Fish</p>', $rendered);
        $this->assertStringNotContainsString('<script', $rendered);
        $this->assertStringNotContainsString('onerror', $rendered);
    }

    public function test_svg_corrupt_image_and_fake_image_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        foreach ([
            UploadedFile::fake()->createWithContent('fish.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->createWithContent('fish.png', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->createWithContent('fish.jpg', "\xff\xd8\xffinvalid-jpeg"),
            UploadedFile::fake()->createWithContent('fish.png', '<?php echo "bad";'),
        ] as $file) {
            $this->postJson('/artikel', array_merge($this->data(), ['gambar' => $file]))->assertUnprocessable()->assertJsonValidationErrors('gambar');
        }
        $this->assertDatabaseCount('artikel', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_storage_failure_returns_localized_generic_error_without_db_record(): void
    {
        $this->actingAs(User::factory()->create());
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->twice()->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        foreach (['id', 'en'] as $locale) {
            $expected = (require lang_path($locale.'/article.php'))['messages']['create_failed'];
            $this->withSession(['locale' => $locale])->postJson('/artikel', array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('safe.png')]))->assertStatus(500)->assertJsonPath('message', $expected);
        }
        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_successful_replacement_and_deletion_cleanup_only_after_persistence(): void
    {
        // FilesystemAdapter dari fake(): IDE kenal assertExists/assertMissing.
        $disk = Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/artikel', array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('old.jpg')]))->assertCreated()->json('data.id');
        $old = Artikel::findOrFail($id)->gambar;
        $this->putJson('/artikel/'.$id, array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('new.png')]))->assertOk();
        $new = Artikel::findOrFail($id)->gambar;
        $disk->assertMissing($old);
        $disk->assertExists($new);
        $this->deleteJson('/artikel/'.$id)->assertOk();
        $disk->assertMissing($new);
    }

    public function test_post_write_db_exception_rolls_back_update_and_keeps_old_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/artikel', array_merge($this->data(), ['gambar' => UploadedFile::fake()->image('old.jpg')]))->assertCreated()->json('data.id');
        $old = Artikel::findOrFail($id)->gambar;
        Artikel::updated(fn () => throw new RuntimeException('Post-write DB failure'));
        $this->putJson('/artikel/'.$id, array_merge($this->data('<p>Changed</p>'), ['gambar' => UploadedFile::fake()->image('new.png')]))->assertStatus(500);
        $row = Artikel::findOrFail($id);
        $this->assertSame($old, $row->gambar);
        $this->assertStringContainsString('Informasi', $row->isi);
        $this->assertSame([$old], Storage::disk('public')->allFiles('artikel-images'));
    }

    public function test_oversized_dimensions_are_rejected_before_decoding(): void
    {
        // A valid IHDR with large dimensions must be rejected before allocating pixels.
        $tiny = UploadedFile::fake()->image('tiny.png');
        $png = file_get_contents($tiny->getRealPath());
        $header = pack('NN', 3000, 3000).substr($png, 24, 5);
        $png = substr_replace($png, $header, 16, 13);
        $png = substr_replace($png, pack('N', crc32('IHDR'.$header)), 29, 4);
        $file = UploadedFile::fake()->createWithContent('large.png', $png);
        $this->actingAs(User::factory()->create())->postJson('/artikel', array_merge($this->data(), ['gambar' => $file]))->assertUnprocessable()->assertJsonPath('errors.gambar.0', 'Resolusi gambar artikel maksimal 8 megapiksel.');
        $this->assertDatabaseCount('artikel', 0);
    }
}
