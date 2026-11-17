<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Database\Seeders\ArticleDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ArticleDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_provides_a_login_and_articles_for_all_moderation_states(): void
    {
        $this->seed(ArticleDemoSeeder::class);

        $author = User::where('email', 'penulis-demo@anufish.test')->sole();
        $this->assertSame('user', $author->role);
        $this->post(route('login.store'), ['email' => $author->email, 'password' => 'anufish-demo-local'])
            ->assertRedirect(route('homepage'));
        $this->assertAuthenticatedAs($author);
        $this->assertSame(
            ['disetujui', 'ditolak', 'menunggu', 'menunggu'],
            Artikel::where('user_id', $author->id)->orderBy('status')->pluck('status')->all(),
        );
    }

    public function test_reseeding_preserves_moderated_and_deleted_articles(): void
    {
        $this->seed(ArticleDemoSeeder::class);

        [$approved, $deleted] = Artikel::where('status', 'menunggu')->orderBy('id')->get();
        $approved->status = 'disetujui';
        $approved->moderation_revision++;
        $approved->save();
        $deleted->delete();

        $this->seed(ArticleDemoSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('artikel', 4);
        $this->assertDatabaseHas('artikel', [
            'id' => $approved->id, 'status' => 'disetujui', 'moderation_revision' => 2,
        ]);
        $this->assertSoftDeleted($deleted);
    }

    public function test_demo_seed_refuses_to_write_outside_local_and_testing(): void
    {
        app()->instance('env', 'production');

        try {
            $this->artisan('db:seed', ['--class' => ArticleDemoSeeder::class, '--force' => true])->run();
            $this->fail('Expected demo seeding to be refused.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('local atau testing', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('artikel', 0);
    }
}
