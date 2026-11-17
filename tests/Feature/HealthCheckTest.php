<?php

namespace Tests\Feature;

use Illuminate\Database\SQLiteDatabaseDoesNotExistException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_liveness_skips_the_database_and_readiness_queries_it_without_a_session(): void
    {
        config(['session.driver' => 'database']);
        DB::enableQueryLog();

        $this->get('/up')
            ->assertOk()
            ->assertExactJson(['status' => 'up'])
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertCookieMissing(config('session.cookie'));
        $this->assertSame([], DB::getQueryLog());

        $this->get('/ready')
            ->assertOk()
            ->assertExactJson(['status' => 'ready'])
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertCookieMissing(config('session.cookie'));
        $this->assertSame(['SELECT 1'], array_column(DB::getQueryLog(), 'query'));
    }

    public function test_a_broken_database_makes_readiness_fail_without_exposing_configuration(): void
    {
        config([
            'app.debug' => true,
            'session.driver' => 'database',
            'database.default' => 'health_fixture',
            'database.connections.health_fixture' => [
                'driver' => 'sqlite',
                'database' => database_path('health-missing-'.Str::uuid().'.sqlite'),
            ],
        ]);
        Log::spy();

        $this->get('/up')->assertOk()->assertExactJson(['status' => 'up']);

        $this->get('/ready')
            ->assertServiceUnavailable()
            ->assertExactJson(['status' => 'unavailable'])
            ->assertHeaderContains('Cache-Control', 'no-store');
        Log::shouldHaveReceived('warning')->with('health.readiness_failed', [
            'exception_type' => SQLiteDatabaseDoesNotExistException::class,
        ])->once();

        config(['database.connections.health_fixture.database' => ':memory:']);
        DB::purge('health_fixture');

        $this->get('/ready')->assertOk()->assertExactJson(['status' => 'ready']);
    }

    public function test_probes_do_not_call_unavailable_external_providers(): void
    {
        Http::fake(['*' => Http::response(null, 503)]);

        $this->getJson('/up')->assertOk();
        $this->getJson('/ready')->assertOk();

        Http::assertNothingSent();
    }

    public function test_maintenance_preserves_liveness_and_disables_readiness(): void
    {
        config(['app.maintenance.driver' => 'array']);
        $this->app->maintenanceMode()->activate(['status' => 503]);
        DB::enableQueryLog();

        $this->get('/up')->assertOk()->assertExactJson(['status' => 'up']);
        $this->get('/ready')->assertServiceUnavailable()->assertExactJson(['status' => 'unavailable']);
        $this->assertSame([], DB::getQueryLog());
    }
}
