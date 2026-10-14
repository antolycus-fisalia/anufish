<?php

namespace Tests\Feature;

use App\Services\InaturalistService;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\InaturalistFixtures;
use Tests\TestCase;

class FishSearchCacheTest extends TestCase
{
    use InaturalistFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
        config(['services.inaturalist.search_cache_minutes' => 30]);
        app()->setLocale('id');
    }

    public function test_final_results_share_normalized_keys_and_outlive_raw_api_cache(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture()]])]);
        $service = app(InaturalistService::class);
        $first = $service->searchSpecies(' Tuna ');
        Http::assertSentCount(2);

        $this->travel(16)->minutes();
        $this->assertSame($first, $service->searchSpecies('TUNA'));
        Http::assertSentCount(2);

        $this->travel(15)->minutes();
        $this->assertSame($first, $service->searchSpecies('tuna'));
        Http::assertSentCount(4);
    }

    public function test_locale_page_and_page_size_have_separate_final_caches(): void
    {
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture()]])]);
        $service = app(InaturalistService::class);
        $service->searchSpecies('tuna');
        $this->assertSame(2, $service->searchSpecies('tuna', 20, 2)['pagination']['current_page']);
        $this->assertSame(12, $service->searchSpecies('tuna', 12)['pagination']['per_page']);
        app()->setLocale('en');
        $service->searchSpecies('tuna');
        Http::assertSentCount(6);
    }

    public function test_overlapping_search_does_not_send_duplicate_api_requests(): void
    {
        config(['services.inaturalist.timeout' => 0.1]);
        $service = app(InaturalistService::class);
        $overlapping = null;
        $calls = 0;
        Http::fake(function () use ($service, &$overlapping, &$calls) {
            if (++$calls === 1) {
                $overlapping = $service->searchSpecies('TUNA');
            }

            return Http::response(['results' => [$this->taxonFixture()]]);
        });

        // The second request overlaps the first while it owns the shared lock.
        $first = $service->searchSpecies('tuna');
        $this->assertSame(429, $overlapping['status']);
        $this->assertTrue($first['success']);
        $this->assertSame($first, $service->searchSpecies('tuna'));
        Http::assertSentCount(2);
    }

    public function test_failed_request_is_not_cached_and_releases_the_lock(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 503)->push(['results' => [$this->taxonFixture()]])->push(['results' => [$this->taxonFixture()]])]);
        $service = app(InaturalistService::class);
        $this->assertFalse($service->searchSpecies('tuna')['success']);
        $this->assertTrue($service->searchSpecies('tuna')['success']);
        Http::assertSentCount(3);
    }

    public function test_waiting_search_reads_results_published_by_the_lock_owner(): void
    {
        $expected = ['success' => true, 'status' => 200, 'message' => null, 'data' => [],
            'pagination' => ['current_page' => 1, 'per_page' => 20, 'has_more' => false]];
        $lock = \Mockery::mock(Lock::class);
        $lock->shouldReceive('get')->once()->andReturn(false);
        Cache::shouldReceive('get')->twice()->andReturn(null, $expected);
        Cache::shouldReceive('lock')->once()->andReturn($lock);

        $this->assertSame($expected, app(InaturalistService::class)->searchSpecies('tuna'));
        Http::assertNothingSent();
    }

    public function test_search_cache_and_locks_work_with_the_database_store(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Cache::setDefaultDriver('database');
        Http::fake(['*' => Http::response(['results' => [$this->taxonFixture()]])]);
        $service = app(InaturalistService::class);
        $first = $service->searchSpecies('tuna');
        $this->assertTrue($first['success']);
        $this->assertSame($first, $service->searchSpecies('TUNA'));
        Http::assertSentCount(2);
        $this->assertDatabaseCount('cache_locks', 0);
    }

    public function test_failed_enrichment_is_not_saved_as_a_complete_search(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['results' => [$this->taxonFixture(['names' => []])]])
            ->push([], 503)
            ->push(['results' => [$this->taxonFixture()]])]);
        $service = app(InaturalistService::class);
        $this->assertSame([], $service->searchSpecies('tuna')['data']);
        $this->assertCount(1, $service->searchSpecies('tuna')['data']);
        Http::assertSentCount(3);
    }
}
