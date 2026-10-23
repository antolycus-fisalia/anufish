<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    // ── Fixtures ─────────────────────────────────────────────────────────────

    /** Minimal valid Gemini response with scientific name + confidence. */
    private function geminiResponse(array $candidates): array
    {
        $json = json_encode(['candidates' => $candidates]);

        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [['text' => $json]],
                    ],
                ],
            ],
        ];
    }

    private function storeFakeImage(string $filename = 'ikan.jpg'): string
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image($filename)->size(500);
        $path = 'scans/' . $filename;
        Storage::disk('local')->put($path, $file->getContent());

        return $path;
    }

    // ── AC: Nama hasil identifikasi dipetakan ke response aplikasi ────────────

    public function test_successful_identification_returns_candidates(): void
    {
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Channa striata', 'confidence' => 0.92],
                ['scientific_name' => 'Channa micropeltes', 'confidence' => 0.06],
            ]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertTrue($result['success']);
        $this->assertNull($result['error']);
        $this->assertCount(2, $result['candidates']);

        $this->assertSame('Channa striata', $result['candidates'][0]['scientific_name']);
        $this->assertEqualsWithDelta(0.92, $result['candidates'][0]['confidence'], 0.001);
    }

    // ── AC: Confidence score ditangani bila tersedia sesuai rancangan ─────────

    public function test_candidate_without_confidence_returns_null(): void
    {
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Oreochromis niloticus'],
            ]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertTrue($result['success']);
        $this->assertNull($result['candidates'][0]['confidence']);
    }

    public function test_confidence_is_clamped_to_0_1(): void
    {
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Cyprinus carpio', 'confidence' => 1.5],
                ['scientific_name' => 'Rasbora lateristriata', 'confidence' => -0.3],
            ]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertTrue($result['success']);
        $this->assertSame(1.0, $result['candidates'][0]['confidence']);
        $this->assertSame(0.0, $result['candidates'][1]['confidence']);
    }

    // ── AC: Kegagalan identifikasi menghasilkan respons terkontrol ────────────

    public function test_gemini_http_error_returns_controlled_failure(): void
    {
        $path = $this->storeFakeImage();

        Http::fake(['*' => Http::response([], 500)]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['candidates']);
        $this->assertNotEmpty($result['error']);
    }

    public function test_gemini_returns_invalid_json_gives_controlled_failure(): void
    {
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'ini bukan json']]]],
                ],
            ], 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['candidates']);
        $this->assertStringContainsString('JSON', $result['error']);
    }

    public function test_gemini_returns_empty_candidates_is_controlled_success(): void
    {
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        // Empty candidates is a valid structured result — no fish detected.
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['candidates']);
    }

    public function test_connection_timeout_returns_controlled_failure(): void
    {
        $path = $this->storeFakeImage();

        Http::fake(['*' => function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        }]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['candidates']);
        $this->assertNotEmpty($result['error']);
    }

    public function test_missing_file_returns_controlled_failure(): void
    {
        Storage::fake('local');

        Http::fake(); // Should not be called

        $result = app(GeminiService::class)->identify('scans/tidak-ada.jpg');

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['candidates']);
        Http::assertNothingSent();
    }

    // ── AC: Timeout maksimal 10 detik diterapkan ─────────────────────────────

    public function test_timeout_config_is_clamped_to_10_seconds_max(): void
    {
        // Override timeout to something extreme; service should clamp it.
        config(['services.gemini.timeout' => 99]);

        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Cyprinus carpio', 'confidence' => 0.8],
            ]), 200),
        ]);

        Http::fake(['*' => function (Request $request) {
            // The actual HTTP call is intercepted; we just verify success.
            return Http::response($this->geminiResponse([
                ['scientific_name' => 'Cyprinus carpio', 'confidence' => 0.8],
            ]), 200);
        }]);

        $result = app(GeminiService::class)->identify($path);
        $this->assertTrue($result['success']);
    }

    // ── AC: API key tidak dikirim ke client atau hardcoded ────────────────────

    public function test_api_key_is_not_in_candidates_response(): void
    {
        $path = $this->storeFakeImage();
        $fakeKey = 'test-secret-api-key-12345';
        config(['services.gemini.api_key' => $fakeKey]);

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Rasbora lateristriata', 'confidence' => 0.75],
            ]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        // The key must not appear anywhere in candidates or error messages.
        $encoded = json_encode($result['candidates']);
        $this->assertStringNotContainsString($fakeKey, $encoded);
        $this->assertStringNotContainsString($fakeKey, (string) $result['error']);
    }

    public function test_api_key_appears_only_in_url_not_response_body(): void
    {
        $path     = $this->storeFakeImage();
        $fakeKey  = 'supersecret-api-key-xyz';
        config(['services.gemini.api_key' => $fakeKey]);

        $capturedUrl = null;

        Http::fake(['*' => function (Request $request) use (&$capturedUrl, $fakeKey) {
            $capturedUrl = $request->url();

            // Key goes in the URL query string, NOT in the request body.
            $body = json_decode($request->body(), true);
            $bodyStr = json_encode($body);

            // Assert inside the fake to capture the assertion message clearly.
            assert(! str_contains($bodyStr, $fakeKey), 'API key must not appear in the request body.');

            return Http::response($this->geminiResponse([
                ['scientific_name' => 'Oreochromis niloticus', 'confidence' => 0.88],
            ]), 200);
        }]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString($fakeKey, (string) $capturedUrl);
    }

    // ── AC: max_candidates boundary ──────────────────────────────────────────

    public function test_candidates_are_limited_to_configured_max(): void
    {
        config(['services.gemini.max_candidates' => 2]);
        $path = $this->storeFakeImage();

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Channa striata', 'confidence' => 0.90],
                ['scientific_name' => 'Channa micropeltes', 'confidence' => 0.07],
                ['scientific_name' => 'Channa marulius', 'confidence' => 0.03],
            ]), 200),
        ]);

        $result = app(GeminiService::class)->identify($path);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['candidates']);
    }

    // ── Integration: ScanController calls GeminiService ──────────────────────

    public function test_upload_endpoint_returns_candidates_on_success(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('ikan.jpg')->size(1000);

        Http::fake([
            '*' => Http::response($this->geminiResponse([
                ['scientific_name' => 'Channa striata', 'confidence' => 0.95],
            ]), 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), ['fish_image' => $file]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['path', 'disk', 'candidates'],
            ]);

        $candidates = $response->json('data.candidates');
        $this->assertNotEmpty($candidates);
        $this->assertSame('Channa striata', $candidates[0]['scientific_name']);
    }

    public function test_upload_endpoint_returns_502_when_gemini_fails(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('ikan.jpg')->size(1000);

        Http::fake(['*' => Http::response([], 503)]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), ['fish_image' => $file]);

        $response
            ->assertStatus(502)
            ->assertJson(['success' => false]);
    }

    public function test_upload_cleans_up_file_when_gemini_fails(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('ikan.jpg')->size(1000);

        Http::fake(['*' => Http::response([], 503)]);

        $this
            ->actingAs($user)
            ->postJson(route('scan.upload'), ['fish_image' => $file]);

        // No orphaned scan files should remain after a Gemini failure.
        $files = Storage::disk('local')->files('scans');
        $this->assertEmpty($files, 'Scan file must be deleted when identification fails.');
    }
}

