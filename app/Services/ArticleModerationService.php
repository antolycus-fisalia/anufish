<?php

namespace App\Services;

use App\Events\ArtikelModerated;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ArticleModerationService
{
    /**
     * Transisi status yang sah: menunggu -> disetujui | ditolak.
     * Aksi ulang pada status yang sama atau disetujui -> ditolak = konflik.
     */
    private const ALLOWED_TRANSITIONS = [
        'menunggu' => ['disetujui', 'ditolak'],
    ];

    public function approve(User $admin, Artikel $artikel, int $revision): array
    {
        return $this->transition($admin, $artikel, $revision, 'disetujui', 'approve');
    }

    public function reject(User $admin, Artikel $artikel, int $revision): array
    {
        return $this->transition($admin, $artikel, $revision, 'ditolak', 'reject');
    }

    public function deleteArticle(User $admin, Artikel $artikel, int $revision): array
    {
        try {
            return DB::transaction(function () use ($admin, $artikel, $revision) {
                $locked = $this->lock($artikel, $revision);

                if (! $locked) {
                    return $this->conflict();
                }

                // Kebijakan hapus: soft delete, status tidak diubah.
                if (! $locked->delete()) {
                    throw new RuntimeException('Article was not deleted.');
                }

                $this->afterCommit('delete', $admin, $locked);

                return [
                    'success' => true,
                    'status' => 'ok',
                    'message' => __('article.moderation.deleted'),
                    'data' => $locked->fresh(),
                ];
            });
        } catch (Throwable $e) {
            report($e);

            return $this->failed();
        }
    }

    private function transition(User $admin, Artikel $artikel, int $revision, string $target, string $action): array
    {
        try {
            return DB::transaction(function () use ($admin, $artikel, $revision, $target, $action) {
                $locked = $this->lock($artikel, $revision);

                if (! $locked) {
                    return $this->conflict();
                }

                $allowed = self::ALLOWED_TRANSITIONS[$locked->status] ?? [];

                if (! in_array($target, $allowed, true)) {
                    return $this->conflict();
                }

                $locked->status = $target;
                $locked->moderation_revision++;

                if (! $locked->save()) {
                    throw new RuntimeException('Article was not persisted.');
                }

                $this->afterCommit($action, $admin, $locked);

                return [
                    'success' => true,
                    'status' => 'ok',
                    'message' => __($target === 'disetujui' ? 'article.moderation.approved' : 'article.moderation.rejected'),
                    'data' => $locked->fresh(),
                ];
            });
        } catch (Throwable $e) {
            report($e);

            return $this->failed();
        }
    }

    private function lock(Artikel $artikel, int $revision): ?Artikel
    {
        return Artikel::query()
            ->whereKey($artikel->getKey())
            ->where('moderation_revision', $revision)
            ->lockForUpdate()
            ->first();
    }

    private function conflict(): array
    {
        return [
            'success' => false,
            'status' => 'conflict',
            'message' => __('article.moderation.conflict'),
            'data' => null,
        ];
    }

    private function failed(): array
    {
        return [
            'success' => false,
            'status' => 'failed',
            'message' => __('article.moderation.failed'),
            'data' => null,
        ];
    }

    /**
     * Audit: hanya ID dan aksi, tanpa isi/kondisi sensitif.
     */
    private function audit(string $action, User $admin, Artikel $artikel): void
    {
        Log::info('article.moderated', [
            'action' => $action,
            'actor_id' => $admin->id,
            'artikel_id' => $artikel->id,
        ]);
    }

    private function afterCommit(string $action, User $admin, Artikel $artikel): void
    {
        DB::afterCommit(function () use ($action, $admin, $artikel) {
            $this->audit($action, $admin, $artikel);

            try {
                app(Dispatcher::class)->dispatch(
                    new ArtikelModerated($action, $admin->id, (int) $artikel->getKey())
                );
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}
