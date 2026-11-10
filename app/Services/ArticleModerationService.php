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
        'ditolak' => ['menunggu'],
    ];

    public function approve(User $admin, Artikel $artikel): array
    {
        return $this->transition($admin, $artikel, 'disetujui', 'approve');
    }

    public function reject(User $admin, Artikel $artikel): array
    {
        return $this->transition($admin, $artikel, 'ditolak', 'reject');
    }

    public function deleteArticle(User $admin, Artikel $artikel): array
    {
        try {
            $result = DB::transaction(function () use ($admin, $artikel) {
                $locked = $this->lock($artikel);

                if (! $locked) {
                    return $this->conflict();
                }

                // Kebijakan hapus: soft delete, status tidak diubah.
                if (! $locked->delete()) {
                    throw new RuntimeException('Article was not deleted.');
                }

                $this->audit('delete', $admin, $locked);

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

        if ($result['success']) {
            $this->notify('delete', $admin, $artikel);
        }

        return $result;
    }

    private function transition(User $admin, Artikel $artikel, string $target, string $action): array
    {
        try {
            $result = DB::transaction(function () use ($admin, $artikel, $target, $action) {
                $locked = $this->lock($artikel);

                // Baris tidak ketemu: status sudah berubah -> konflik atomik, bukan menimpa.
                if (! $locked) {
                    return $this->conflict();
                }

                $allowed = self::ALLOWED_TRANSITIONS[$locked->status] ?? [];

                if (! in_array($target, $allowed, true)) {
                    return $this->conflict();
                }

                $locked->status = $target;

                if (! $locked->save()) {
                    throw new RuntimeException('Article was not persisted.');
                }

                $this->audit($action, $admin, $locked);

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

        if ($result['success']) {
            $this->notify($action, $admin, $artikel);
        }

        return $result;
    }

    /**
     * Kunci baris dengan status semacam yang admin lihat (compare-and-set):
     * kalau admin lain menggeser status lebih dulu, query ini kosong.
     */
    private function lock(Artikel $artikel): ?Artikel
    {
        return Artikel::query()
            ->whereKey($artikel->getKey())
            ->where('status', $artikel->status)
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

    /**
     * Event notifikasi HANYA dipanggil setelah DB::transaction kembali
     * (transaksi sudah commit). Listener/channel-nya adalah scope FSH-21.
     */
    private function notify(string $action, User $admin, Artikel $artikel): void
    {
        app(Dispatcher::class)->dispatch(
            new ArtikelModerated($action, $admin->id, (int) $artikel->getKey())
        );
    }
}
