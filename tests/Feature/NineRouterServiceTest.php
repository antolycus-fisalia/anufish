<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NineRouterService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use Tests\TestCase;

class NineRouterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Storage::fake('local');
        config([
            'services.ninerouter.base_url' => 'http://192.168.1.10:20128/v1',
            'services.ninerouter.api_key' => 'test-only-key',
            'services.ninerouter.model' => 'test-vision',
        ]);
    }

    private function image(): string
    {
        return UploadedFile::fake()->image('fish.png')->storeAs('scans', 'fish.png', 'local');
    }

    private function reply(array $candidates): array
    {
        return ['choices' => [['finish_reason' => 'stop', 'message' => ['content' => json_encode(['candidates' => $candidates])]]]];
    }

    public function test_upload_sends_private_image_with_configured_model_and_preserves_contract(): void
    {
        Http::fake([
            '192.168.1.10:20128/*' => Http::response($this->reply([['scientific_name' => 'Channa striata', 'confidence' => 0.9]])),
            'api.inaturalist.org/*' => Http::response(['results' => []]),
        ]);
        $response = $this->actingAs(User::factory()->create())->postJson('/scan/upload', ['fish_image' => UploadedFile::fake()->image('fish.png')]);
        $response->assertCreated()->assertJsonPath('data.candidates.0.scientific_name', 'Channa striata')
            ->assertJsonStructure(['data' => ['path', 'disk', 'size', 'mime_type', 'original_name', 'candidates']]);
        Storage::disk('local')->assertExists($response->json('data.path'));
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '192.168.1.10:20128/')) {
                return false;
            }
            $this->assertSame('http://192.168.1.10:20128/v1/chat/completions', $request->url());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer test-only-key'));
            $this->assertSame('test-vision', $request['model']);
            $this->assertStringStartsWith('data:image/png;base64,', $request['messages'][1]['content'][1]['image_url']['url']);
            $this->assertStringNotContainsString('test-only-key', $request->body());

            return true;
        });
        $this->assertStringNotContainsString('test-only-key', $response->getContent());
    }

    public function test_empty_candidates_are_unknown_and_missing_confidence_is_null(): void
    {
        $path = $this->image();
        Http::fake(['*' => Http::sequence()->push($this->reply([]))->push($this->reply([['scientific_name' => 'Channa striata']]))]);
        $service = app(NineRouterService::class);
        $this->assertSame(['success' => true, 'candidates' => [], 'error' => null], $service->identify($path));
        $this->assertNull($service->identify($path)['candidates'][0]['confidence']);
    }

    public function test_webp_upload_is_sent_as_jpeg_and_the_private_original_is_preserved(): void
    {
        $photo = UploadedFile::fake()->image('fish.webp', 80, 80);
        $original = file_get_contents($photo->getRealPath());
        Http::fake(['*' => Http::response($this->reply([['scientific_name' => 'Channa striata']]))]);

        $response = $this->actingAs(User::factory()->create())
            ->postJson('/scan/upload', ['fish_image' => $photo]);
        $response->assertCreated()->assertJsonPath('data.mime_type', 'image/webp');
        $this->assertSame($original, Storage::disk('local')->get($response->json('data.path')));
        Http::assertSent(function ($request) {
            $url = $request['messages'][1]['content'][1]['image_url']['url'];
            $this->assertStringStartsWith('data:image/jpeg;base64,', $url);
            $bytes = base64_decode(explode(',', $url, 2)[1], true);
            $this->assertSame(IMAGETYPE_JPEG, getimagesizefromstring($bytes)[2]);

            return true;
        });
    }

    public function test_https_verification_uses_configured_ca_bundle(): void
    {
        $path = $this->image();
        $bundle = base_path('resources/certificates/cacert.pem');
        config(['services.ninerouter.ca_bundle' => $bundle]);
        $verification = null;
        Http::fake(function ($request, $options) use (&$verification) {
            $verification = $options['verify'] ?? null;

            return Http::response($this->reply([]));
        });
        $this->assertTrue(app(NineRouterService::class)->identify($path)['success']);
        $this->assertSame($bundle, $verification);
    }

    public function test_large_photo_is_reduced_without_modifying_private_original(): void
    {
        $original = file_get_contents(public_path('images/clownfish.jpg'));
        Storage::disk('local')->put('scans/fish.jpg', $original);
        Http::fake(['*' => Http::response($this->reply([]))]);
        $this->assertTrue(app(NineRouterService::class)->identify('scans/fish.jpg')['success']);
        Http::assertSent(function ($request) use ($original) {
            $url = $request['messages'][1]['content'][1]['image_url']['url'];
            $this->assertStringStartsWith('data:image/jpeg;base64,', $url);
            $image = base64_decode(explode(',', $url, 2)[1]);
            $dimensions = getimagesizefromstring($image);
            $this->assertLessThanOrEqual(1024, max($dimensions[0], $dimensions[1]));
            $this->assertLessThan(strlen($original), strlen($image));

            return true;
        });
        $this->assertSame($original, Storage::disk('local')->get('scans/fish.jpg'));
    }

    public function test_timeout_is_localized_and_configured_above_ten_seconds(): void
    {
        config(['services.ninerouter.timeout' => 45]);
        $path = $this->image();
        $requestOptions = [];
        Http::fake(function ($request, $options) use (&$requestOptions) {
            $requestOptions = $options;
            throw new ConnectionException('cURL error 28: Operation timed out');
        });
        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);
            $result = app(NineRouterService::class)->identify($path);
            $this->assertSame(trans('scan.errors.timeout', [], $locale), $result['error']);
        }
        $this->assertSame(45.0, $requestOptions['timeout']);
        $this->assertFalse($requestOptions['expect']);
    }

    public function test_php_execution_budget_outlasts_provider_timeout_and_is_restored(): void
    {
        $original = (int) ini_get('max_execution_time');
        $path = $this->image();
        config(['services.ninerouter.timeout' => 45]);
        try {
            set_time_limit(30);
            Http::fake(function () {
                $this->assertGreaterThan(45, (int) ini_get('max_execution_time'));

                return Http::response($this->reply([]));
            });
            $this->assertTrue(app(NineRouterService::class)->identify($path)['success']);
            $this->assertSame(30, (int) ini_get('max_execution_time'));

            Http::fake(function () {
                $this->assertGreaterThan(45, (int) ini_get('max_execution_time'));
                throw new ConnectionException('cURL error 28: Operation timed out');
            });
            $this->assertSame(__('scan.errors.timeout'), app(NineRouterService::class)->identify($path)['error']);
            $this->assertSame(30, (int) ini_get('max_execution_time'));
        } finally {
            set_time_limit($original);
        }
    }

    public function test_malformed_responses_are_controlled_failures(): void
    {
        $path = $this->image();
        $values = ['"scalar"', '{}', '{"candidates":"bad"}', '{"candidates":[{"scientific_name":[]}]}', '{"candidates":[{"scientific_name":"tuna"}]}', '{"candidates":[{"scientific_name":"Channa striata","confidence":2}]}', '{"candidates":[{"scientific_name":"Channa striata","confidence":true}]}', 'not json'];
        foreach ($values as $text) {
            Http::fake(['*' => Http::response(['choices' => [['finish_reason' => 'stop', 'message' => ['content' => $text]]]])]);
            $this->assertFalse(app(NineRouterService::class)->identify($path)['success'], $text);
        }
        foreach (['"scalar"', '[]', '{"choices":"bad"}', '{"choices":[{"finish_reason":"length"}]}'] as $raw) {
            Http::fake(['*' => Http::response($raw, 200, ['Content-Type' => 'application/json'])]);
            $this->assertFalse(app(NineRouterService::class)->identify($path)['success']);
        }
    }

    public function test_missing_configuration_does_not_send_image(): void
    {
        foreach (['base_url', 'api_key', 'model'] as $field) {
            config([
                'services.ninerouter.base_url' => 'http://192.168.1.10:20128/v1',
                'services.ninerouter.api_key' => 'test',
                'services.ninerouter.model' => 'test',
                'services.ninerouter.'.$field => '',
            ]);
            $this->assertSame(__('scan.errors.configuration'), app(NineRouterService::class)->identify($this->image())['error']);
        }
        Http::assertNothingSent();
    }

    public function test_base_url_accepts_trailing_slash_and_rejects_invalid_urls(): void
    {
        $path = $this->image();
        config(['services.ninerouter.base_url' => ' http://192.168.1.10:20128/v1/ ']);
        Http::fake(['*' => Http::response($this->reply([]))]);
        $this->assertTrue(app(NineRouterService::class)->identify($path)['success']);
        Http::assertSent(fn ($request) => $request->url() === 'http://192.168.1.10:20128/v1/chat/completions');

        Http::fake();
        foreach (['192.168.1.10:20128/v1', 'ftp://192.168.1.10/v1', 'not a URL'] as $url) {
            config(['services.ninerouter.base_url' => $url]);
            $this->assertSame(__('scan.errors.configuration'), app(NineRouterService::class)->identify($path)['error']);
        }
        Http::assertNothingSent();
    }

    public function test_file_read_error_is_controlled(): void
    {
        $disk = \Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->andReturn(true);
        $disk->shouldReceive('get')->andThrow(UnableToReadFile::fromLocation('scans/fish.png'));
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        $this->assertFalse(app(NineRouterService::class)->identify('scans/fish.png')['success']);
        Http::assertNothingSent();
    }

    public function test_provider_failure_is_localized_and_failed_upload_is_deleted(): void
    {
        foreach (['id', 'en'] as $locale) {
            Http::fake(['*' => Http::response(['error' => 'private upstream diagnostic'], 503)]);
            $response = $this->actingAs(User::factory()->create())->withSession(['locale' => $locale])
                ->postJson('/scan/upload', ['fish_image' => UploadedFile::fake()->image('fish.png')]);
            $response->assertStatus(502)->assertJsonPath('message', trans('scan.errors.provider', [], $locale));
            $this->assertStringNotContainsString('private upstream diagnostic', $response->getContent());
            $this->assertSame([], Storage::disk('local')->allFiles('scans'));
        }
    }

    public function test_rate_limit_and_timeout_are_controlled_without_retry(): void
    {
        $path = $this->image();
        Http::fake(['*' => Http::response([], 429)]);
        $this->assertSame(__('scan.errors.rate_limit'), app(NineRouterService::class)->identify($path)['error']);
        Http::assertSentCount(1);
        Http::fake(fn () => throw new ConnectionException('private connection detail'));
        $this->assertSame(__('scan.errors.connection'), app(NineRouterService::class)->identify($path)['error']);
    }
}
