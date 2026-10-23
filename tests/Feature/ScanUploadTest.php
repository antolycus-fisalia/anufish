<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanUploadTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper ───────────────────────────────────────────────────────────────

    /**
     * Return a faked Gemini HTTP response so storage tests do not
     * depend on the live Gemini API.
     */
    private function fakeGeminiSuccess(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [[
                                'text' => json_encode([
                                    'candidates' => [
                                        ['scientific_name' => 'Channa striata', 'confidence' => 0.9],
                                    ],
                                ]),
                            ]],
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    // ──────────────────────────────────────────────
    // Success scenarios
    // ──────────────────────────────────────────────

    public function test_valid_jpg_is_uploaded_and_stored(): void
    {
        Storage::fake('local');
        $this->fakeGeminiSuccess();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('foto-ikan.jpg')->size(1000);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['path', 'disk', 'original_name', 'mime_type', 'candidates'],
            ]);

        $path = $response->json('data.path');

        // File is stored inside the "scans" directory.
        $this->assertStringStartsWith('scans/', $path);

        // File actually exists on disk.
        $this->assertTrue(
            Storage::disk('local')->exists($path)
        );
    }

    public function test_valid_png_is_uploaded_and_stored(): void
    {
        Storage::fake('local');
        $this->fakeGeminiSuccess();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('foto-ikan.png')->size(2000);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertTrue(
            Storage::disk('local')->exists($response->json('data.path'))
        );
    }

    public function test_valid_jpeg_is_uploaded_and_stored(): void
    {
        Storage::fake('local');
        $this->fakeGeminiSuccess();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('foto-ikan.jpeg')->size(500);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertTrue(
            Storage::disk('local')->exists($response->json('data.path'))
        );
    }

    // ──────────────────────────────────────────────
    // Filename safety — AC: Nama file tidak bergantung pada nama asli user
    // ──────────────────────────────────────────────

    public function test_stored_filename_does_not_match_original_name(): void
    {
        Storage::fake('local');
        $this->fakeGeminiSuccess();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('my-secret-name.jpg')->size(500);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(200);

        $path = $response->json('data.path');
        $storedFilename = basename($path);

        // The stored filename must NOT be the original upload name.
        $this->assertNotEquals('my-secret-name.jpg', $storedFilename);

        // The stored filename should be a UUID pattern.
        $nameWithoutExtension = pathinfo($storedFilename, PATHINFO_FILENAME);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $nameWithoutExtension,
            'Stored filename should be a UUID.'
        );
    }

    // ──────────────────────────────────────────────
    // Failure scenarios — AC: File di atas 5 MB ditolak
    // ──────────────────────────────────────────────

    public function test_file_exceeding_5_mb_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        // 6000 KB = ~5.86 MB, above the 5 MB (5120 KB) limit.
        $file = UploadedFile::fake()->image('big-fish.jpg')->size(6000);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    public function test_file_at_exactly_5_mb_is_accepted(): void
    {
        Storage::fake('local');
        $this->fakeGeminiSuccess();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('exact-limit.jpg')->size(5120);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // ──────────────────────────────────────────────
    // Failure scenarios — AC: Ekstensi/MIME tidak valid ditolak
    // ──────────────────────────────────────────────

    public function test_gif_file_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('fish.gif')->size(500);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    public function test_webp_file_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('fish.webp')->size(500);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    public function test_pdf_file_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    public function test_text_file_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('notes.txt', 100, 'text/plain');

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    // ──────────────────────────────────────────────
    // Missing file
    // ──────────────────────────────────────────────

    public function test_missing_file_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('fish_image');
    }

    // ──────────────────────────────────────────────
    // AC: Pesan error validasi jelas
    // ──────────────────────────────────────────────

    public function test_error_message_is_clear_for_oversized_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('big.jpg')->size(6000);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(422);

        $errors = $response->json('errors.fish_image');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('5 MB', $errors[0]);
    }

    public function test_error_message_is_clear_for_wrong_format(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('fish.gif')->size(500);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(422);

        $errors = $response->json('errors.fish_image');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('JPG', $errors[0]);
    }

    // ──────────────────────────────────────────────
    // Auth guard — only logged-in users may upload
    // ──────────────────────────────────────────────

    public function test_guest_cannot_upload_scan(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('fish.jpg')->size(500);

        $response = $this
            ->postJson(route('scan.upload'), [
                'fish_image' => $file,
            ]);

        $response->assertStatus(401);
    }
}
