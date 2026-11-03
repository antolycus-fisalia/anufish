<?php

namespace Tests\Feature;

use App\Models\RiwayatScan;
use App\Models\User;
use App\Services\RuvicodeService;
use App\Services\ScanReferenceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScanHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
    }

    private function identify(array $candidates = [['scientific_name' => 'Channa striata', 'confidence' => null]], bool $success = true): void
    {
        $this->mock(RuvicodeService::class)->shouldReceive('identify')->once()->andReturn([
            'success' => $success, 'candidates' => $candidates, 'error' => 'Provider unavailable',
        ]);
    }

    private function upload(array $extra = [])
    {
        return $this->postJson('/scan/upload', array_merge([
            'fish_image' => UploadedFile::fake()->image('fish.jpg'),
            'operation_id' => (string) Str::uuid(),
        ], $extra));
    }

    private function record(): RiwayatScan
    {
        $this->identify();
        $this->upload()->assertCreated();

        return RiwayatScan::firstOrFail();
    }

    public function test_upload_saves_only_server_identity_and_original_candidates(): void
    {
        $this->identify([['scientific_name' => 'Channa striata', 'confidence' => 0.873215]]);
        $response = $this->upload(['user_id' => User::factory()->create()->id, 'confidence' => 1, 'inaturalist_taxon_id' => 999]);
        $response->assertCreated()->assertJsonPath('data.history_id', RiwayatScan::firstOrFail()->id);
        $row = RiwayatScan::firstOrFail();
        $this->assertSame(auth()->id(), $row->user_id);
        $this->assertSame(0.873215, $row->candidates[0]['confidence']);
        $this->assertNull($row->inaturalist_taxon_id);
        $this->assertNotNull($row->created_at);
        Storage::disk('local')->assertExists($row->image_path);
        Http::assertNothingSent();
    }

    public function test_replaying_same_upload_does_not_call_ai_or_duplicate_image_and_row(): void
    {
        $this->identify();
        $file = UploadedFile::fake()->image('fish.jpg');
        $data = ['fish_image' => $file, 'operation_id' => (string) Str::uuid()];
        $id = $this->upload($data)->assertCreated()->json('data.history_id');
        $this->upload($data)->assertCreated()->assertJsonPath('data.history_id', $id);
        $this->assertDatabaseCount('riwayat_scan', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles('scans'));
    }

    public function test_reusing_operation_for_different_bytes_is_rejected(): void
    {
        $this->identify();
        $operation = (string) Str::uuid();
        $this->upload(['operation_id' => $operation])->assertCreated();
        $this->upload(['operation_id' => $operation, 'fish_image' => UploadedFile::fake()->image('other.jpg', 30, 40)])->assertConflict();
        $this->assertDatabaseCount('riwayat_scan', 1);
    }

    public function test_same_operation_id_is_scoped_to_owner(): void
    {
        $this->mock(RuvicodeService::class)->shouldReceive('identify')->twice()->andReturn(['success' => true, 'candidates' => [['scientific_name' => 'Channa striata']], 'error' => null]);
        $id = (string) Str::uuid();
        $this->upload(['operation_id' => $id])->assertCreated();
        $this->actingAs(User::factory()->create());
        $this->upload(['operation_id' => $id])->assertCreated();
        $this->assertDatabaseCount('riwayat_scan', 2);
    }

    public function test_inflight_operation_rejects_duplicate_before_ai(): void
    {
        $id = (string) Str::uuid();
        $lock = Cache::lock('scan-upload:'.auth()->id().':'.$id, 180);
        $lock->get();
        try {
            $this->upload(['operation_id' => $id])->assertConflict();
            $this->assertDatabaseCount('riwayat_scan', 0);
        } finally {
            $lock->release();
        }
    }

    public function test_unknown_has_no_success_history_and_removes_image(): void
    {
        $this->identify([]);
        $this->upload()->assertCreated()->assertJsonPath('data.history_id', null);
        $this->assertDatabaseCount('riwayat_scan', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles('scans'));
    }

    public function test_ai_failure_does_not_save_history(): void
    {
        $this->identify([], false);
        $this->upload()->assertStatus(502);
        $this->assertDatabaseCount('riwayat_scan', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles('scans'));
    }

    public function test_failed_database_save_does_not_report_success_and_cleans_image(): void
    {
        $this->identify();
        RiwayatScan::saving(fn () => false);
        $this->withSession(['locale' => 'en'])->upload()->assertStatus(500)->assertJsonPath('message', 'Could not save scan history. Please retry.');
        $this->assertDatabaseCount('riwayat_scan', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles('scans'));
    }

    public function test_private_detail_photo_and_reference_enforce_owner(): void
    {
        $row = $this->record();
        $this->get('/scan/history/'.$row->id)->assertOk()->assertSee('Channa striata');
        $this->get('/scan/history/'.$row->id.'/image')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs(User::factory()->create());
        $this->get('/scan/history')->assertOk()->assertDontSee('Channa striata');
        $this->get('/scan/history/'.$row->id)->assertNotFound();
        $this->get('/scan/history/'.$row->id.'/image')->assertNotFound();
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 0])->assertNotFound();
    }

    public function test_selection_uses_server_candidate_and_preserves_missing_confidence(): void
    {
        $row = $this->record();
        $this->mock(ScanReferenceResolver::class)->shouldReceive('enrich')->once()->with($row->candidates)->andReturn([
            ['match_status' => 'matched', 'taxon_id' => 123, 'reference' => [], 'reference_options' => []],
        ]);
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 0, 'scientific_name' => 'Injected species', 'taxon_id' => 999, 'confidence' => 1])->assertOk();
        $row->refresh();
        $this->assertSame('Channa striata', $row->scientific_name);
        $this->assertNull($row->confidence);
        $this->assertEquals(123, $row->inaturalist_taxon_id);
    }

    public function test_real_resolver_does_not_save_mismatched_taxon_id(): void
    {
        $row = $this->record();
        Http::fake(['*' => Http::response(['results' => [['id' => 999, 'name' => 'Other species']], 'total_results' => 1])]);
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 0, 'taxon_id' => 999])->assertOk()->assertJsonPath('data.match_status', 'unavailable');
        $this->assertNull($row->fresh()->inaturalist_taxon_id);
        $this->assertSame('Channa striata', $row->fresh()->scientific_name);
    }

    public function test_provider_failure_keeps_ai_candidates_and_image(): void
    {
        $row = $this->record();
        Http::fake(['*' => Http::response([], 503)]);
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 0])->assertOk()->assertJsonPath('data.match_status', 'unavailable');
        $this->assertSame($row->candidates, $row->fresh()->candidates);
        Storage::disk('local')->assertExists($row->image_path);
    }

    public function test_invalid_selection_and_missing_image_have_controlled_responses(): void
    {
        $row = $this->record();
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 3])->assertUnprocessable();
        Storage::disk('local')->delete($row->image_path);
        $this->get('/scan/history/'.$row->id.'/image')->assertNotFound();
        $this->get('/scan/history/'.$row->id)->assertOk();
    }

    public function test_history_details_are_localized_and_do_not_expose_private_paths(): void
    {
        $row = $this->record();
        foreach (['id' => 'Kandidat hasil AI', 'en' => 'AI candidates'] as $locale => $label) {
            $this->withSession(['locale' => $locale])->get('/scan/history/'.$row->id)->assertOk()->assertSee($label)->assertDontSee($row->image_path);
        }
    }

    public function test_failed_reference_save_preserves_previous_database_state(): void
    {
        $row = $this->record();
        Http::fake(['*' => Http::response([], 503)]);
        RiwayatScan::saving(fn () => false);
        $this->postJson('/scan/history/'.$row->id.'/reference', ['candidate_index' => 0])->assertStatus(500);
        $this->assertNull($row->fresh()->scientific_name);
        $this->assertSame($row->candidates, $row->fresh()->candidates);
        Storage::disk('local')->assertExists($row->image_path);
    }

    public function test_operation_validation_is_localized_without_saving(): void
    {
        foreach (['id' => 'Identitas operasi scan tidak valid. Pilih ulang foto.', 'en' => 'Invalid scan operation ID. Please select the photo again.'] as $locale => $message) {
            $this->withSession(['locale' => $locale])->upload(['operation_id' => 'invalid'])->assertUnprocessable()->assertJsonPath('errors.operation_id.0', $message);
        }
        $this->assertDatabaseCount('riwayat_scan', 0);
    }
}
