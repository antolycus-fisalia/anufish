<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use App\Notifications\ArtikelModerationNotification;
use App\Services\FishDetailService;
use App\Services\InaturalistService;
use App\Services\NineRouterService;
use App\Services\ScanReferenceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function authenticate(?User $user = null): User
    {
        $user ??= User::factory()->create();
        Auth::forgetGuards();
        $this->withToken($user->createToken('test')->plainTextToken);

        return $user;
    }

    private function article(User $owner, string $status = 'menunggu'): Artikel
    {
        $article = new Artikel(['judul' => 'Ikan Tuna', 'kategori' => 'Edukasi', 'isi' => '<p>Informasi ikan.</p>']);
        $article->user_id = $owner->id;
        $article->status = $status;
        $article->save();

        return $article;
    }

    public function test_home_profile_and_webp_profile_update_return_json(): void
    {
        $user = $this->authenticate();
        $this->get('/api/v1/home')->assertOk()->assertJsonPath('data.latest_article', null);
        $this->get('/api/v1/profile')->assertOk()->assertJsonPath('data.id', $user->id);
        $response = $this->put('/api/v1/profile', [
            'name' => 'Updated User', 'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('avatar.webp', 80, 80),
            'role' => 'admin',
        ])->assertOk()->assertJsonPath('data.name', 'Updated User')
            ->assertJsonPath('data.role', 'user')->assertJsonMissingPath('data.password');
        $this->assertNotNull($response->json('data.photo_url'));
        Storage::disk('public')->assertExists($user->fresh()->profile_photo_path);
    }

    public function test_own_article_crud_sanitizes_html_and_cannot_override_owner_or_status(): void
    {
        $user = $this->authenticate();
        $data = [
            'judul' => 'Ikan WebP', 'kategori' => 'Edukasi',
            'isi' => '<p>Informasi ikan.</p><script>alert(1)</script>',
            'gambar' => UploadedFile::fake()->image('fish.webp'),
            'user_id' => 999, 'status' => 'disetujui',
        ];
        $response = $this->post('/api/v1/my/articles', $data)->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)->assertJsonPath('data.status', 'menunggu');
        $id = $response->json('data.id');
        $this->assertStringNotContainsString('<script>', $response->json('data.isi'));
        Storage::disk('public')->assertExists($response->json('data.gambar'));
        $this->get('/api/v1/my/articles')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->get('/api/v1/my/articles/'.$id)->assertOk()->assertJsonPath('data.id', $id);
        unset($data['gambar']);
        $data['judul'] = 'Updated';
        $this->put('/api/v1/my/articles/'.$id, $data)->assertOk()->assertJsonPath('data.judul', 'Updated');
        $this->delete('/api/v1/my/articles/'.$id)->assertOk();
        $this->assertSoftDeleted('artikel', ['id' => $id]);
    }

    public function test_article_ownership_and_published_visibility_are_enforced(): void
    {
        $owner = User::factory()->create();
        $pending = $this->article($owner);
        $published = $this->article($owner, 'disetujui');
        $this->authenticate();
        $this->get('/api/v1/my/articles')->assertOk()->assertJsonCount(0, 'data');
        $this->get('/api/v1/my/articles/'.$pending->id)->assertForbidden();
        $this->put('/api/v1/my/articles/'.$pending->id, [])->assertForbidden();
        $this->delete('/api/v1/my/articles/'.$pending->id)->assertForbidden();
        $this->get('/api/v1/articles')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $published->id);
        $this->get('/api/v1/articles/'.$pending->id)->assertNotFound();
        $this->get('/api/v1/articles/'.$published->id)->assertOk()->assertJsonPath('data.id', $published->id);
        $published->delete();
        $this->get('/api/v1/articles/'.$published->id)->assertNotFound();
    }

    public function test_fish_search_details_extras_and_photos_use_existing_services(): void
    {
        $this->authenticate();
        $this->mock(InaturalistService::class)->shouldReceive('searchSpecies')->once()
            ->with('tuna', 20, 1)->andReturn(['success' => true, 'data' => [['id' => 12]], 'status' => 200]);
        $this->get('/api/v1/fish/search')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->get('/api/v1/fish/search?q=tuna')->assertOk()->assertJsonPath('data.0.id', 12);
        $this->get('/api/v1/fish')->assertOk()->assertJsonPath('data', []);
        $details = $this->mock(FishDetailService::class);
        $details->shouldReceive('find')->once()->with(12)->andReturn(['status' => 200, 'fish' => ['id' => 12]]);
        $details->shouldReceive('enrich')->once()->with(12)->andReturn(['status' => 200, 'image' => null]);
        $details->shouldReceive('photos')->once()->with(12, null)->andReturn(['status' => 200, 'photos' => []]);
        $this->get('/api/v1/fish/taxa/12')->assertOk()->assertJsonPath('data.id', 12);
        $this->get('/api/v1/fish/taxa/12/extras')->assertOk()->assertJsonPath('image', null);
        $this->get('/api/v1/fish/taxa/12/photos')->assertOk()->assertJsonPath('photos', []);
        $this->get('/api/v1/fish/taxa/not-a-number')->assertNotFound();
    }

    public function test_scan_upload_history_image_and_reference_work_with_tokens_and_preserve_ownership(): void
    {
        $owner = $this->authenticate();
        $this->mock(NineRouterService::class)->shouldReceive('identify')->once()->andReturn([
            'success' => true, 'error' => null,
            'candidates' => [['scientific_name' => 'Channa striata', 'confidence' => 0.9]],
        ]);
        $data = ['operation_id' => (string) Str::uuid(), 'fish_image' => UploadedFile::fake()->image('fish.webp')];
        $response = $this->post('/api/v1/scan/upload', $data)->assertCreated();
        $id = $response->json('data.history_id');
        $this->assertSame(url('/api/v1/scan/history/'.$id), $response->json('data.history_url'));
        $this->post('/api/v1/scan/upload', $data)->assertCreated()->assertJsonPath('data.history_id', $id);
        $this->get('/api/v1/scan/history')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->get('/api/v1/scan/history/'.$id)->assertOk()
            ->assertJsonPath('data.image_url', url('/api/v1/scan/history/'.$id.'/image'))
            ->assertJsonMissingPath('data.image_path')->assertJsonMissingPath('data.image_hash');
        $this->get('/api/v1/scan/history/'.$id.'/image')->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->mock(ScanReferenceResolver::class)->shouldReceive('enrich')->once()->andReturn([[
            'scientific_name' => 'Channa striata', 'match_status' => 'unavailable',
        ]]);
        $this->post('/api/v1/scan/history/'.$id.'/reference', ['candidate_index' => 0])->assertOk()
            ->assertJsonPath('data.match_status', 'unavailable');

        $this->authenticate();
        $this->get('/api/v1/scan/history')->assertOk()->assertJsonCount(0, 'data');
        $this->get('/api/v1/scan/history/'.$id)->assertNotFound();
        $this->get('/api/v1/scan/history/'.$id.'/image')->assertNotFound();
        $this->post('/api/v1/scan/history/'.$id.'/reference', ['candidate_index' => 0])->assertNotFound();
        $this->assertDatabaseHas('riwayat_scan', ['id' => $id, 'user_id' => $owner->id]);
    }

    public function test_notifications_only_expose_and_update_the_authenticated_users_records(): void
    {
        $owner = $this->authenticate();
        $article = $this->article($owner);
        $owner->notify(new ArtikelModerationNotification('approve', 'disetujui', $article->id, $article->judul));
        $notification = $owner->notifications()->firstOrFail();
        $this->get('/api/v1/notifications')->assertOk()->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.link', url('/api/v1/my/articles/'.$article->id));
        $this->post('/api/v1/notifications/'.$notification->id.'/read')->assertOk()->assertJsonPath('data.read', true);
        $owner->notify(new ArtikelModerationNotification('reject', 'ditolak', $article->id, $article->judul));
        $this->post('/api/v1/notifications/read-all')->assertOk();
        $this->get('/api/v1/notifications')->assertOk()->assertJsonPath('unread_count', 0);
        $this->authenticate();
        $this->get('/api/v1/notifications')->assertOk()->assertJsonCount(0, 'data');
        $this->post('/api/v1/notifications/'.$notification->id.'/read')->assertNotFound();
    }

    public function test_admin_routes_reject_regular_users(): void
    {
        $user = $this->authenticate();
        $article = $this->article($user);
        foreach (['/dashboard', '/users', '/users/'.$user->id, '/articles', '/articles/'.$article->id] as $path) {
            $this->get('/api/v1/admin'.$path)->assertForbidden();
        }
        $this->put('/api/v1/admin/users/'.$user->id, [])->assertForbidden();
        foreach (['approve', 'reject', 'delete'] as $action) {
            $this->post('/api/v1/admin/articles/'.$article->id.'/'.$action, ['moderation_revision' => 1])->assertForbidden();
        }
    }

    public function test_admin_reads_updates_and_moderation_preserve_conflict_checks(): void
    {
        $owner = User::factory()->create();
        $article = $this->article($owner);
        $admin = $this->authenticate(User::factory()->create(['role' => 'admin']));
        $this->get('/api/v1/admin/dashboard')->assertOk()->assertJsonPath('data.article_counts.menunggu', 1);
        $this->get('/api/v1/admin/users')->assertOk()->assertJsonCount(2, 'data');
        $this->get('/api/v1/admin/users/'.$owner->id)->assertOk()->assertJsonPath('data.id', $owner->id);
        $this->put('/api/v1/admin/users/'.$owner->id, [
            'name' => 'Updated owner', 'email' => $owner->email, 'role' => 'admin',
        ])->assertOk()->assertJsonPath('data.name', 'Updated owner');
        $this->assertSame('user', $owner->fresh()->role);
        $this->get('/api/v1/admin/articles')->assertOk()->assertJsonPath('data.0.id', $article->id);
        $this->get('/api/v1/admin/articles?status=bad')->assertUnprocessable();
        $this->get('/api/v1/admin/articles?page=999')->assertOk()->assertJsonCount(0, 'data');
        $this->get('/api/v1/admin/articles/'.$article->id)->assertOk()->assertJsonPath('data.moderation_revision', 1);
        $this->post('/api/v1/admin/articles/'.$article->id.'/approve', ['moderation_revision' => 1])
            ->assertOk()->assertJsonPath('data.status', 'disetujui')->assertJsonPath('data.moderation_revision', 2);
        $this->post('/api/v1/admin/articles/'.$article->id.'/reject', ['moderation_revision' => 1])->assertConflict();
        $this->post('/api/v1/admin/articles/'.$article->id.'/delete', ['moderation_revision' => 2])->assertOk();
        $this->assertSoftDeleted('artikel', ['id' => $article->id]);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_reference_lookup_and_successful_rejection_return_json(): void
    {
        $owner = $this->authenticate();
        $this->mock(ScanReferenceResolver::class)->shouldReceive('enrich')->once()
            ->with([['scientific_name' => 'Channa striata']])
            ->andReturn([['scientific_name' => 'Channa striata', 'match_status' => 'matched', 'taxon_id' => 12]]);
        $this->get('/api/v1/scan/reference')->assertUnprocessable();
        $this->get('/api/v1/scan/reference?scientific_name=Channa%20striata')->assertOk()
            ->assertJsonPath('data.taxon_id', 12);
        $article = $this->article($owner);
        $this->authenticate(User::factory()->create(['role' => 'admin']));
        $this->post('/api/v1/admin/articles/'.$article->id.'/reject', [])->assertUnprocessable();
        $this->post('/api/v1/admin/articles/'.$article->id.'/reject', ['moderation_revision' => 1])
            ->assertOk()->assertJsonPath('data.status', 'ditolak')->assertJsonPath('data.moderation_revision', 2);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $owner->id]);
    }
}
