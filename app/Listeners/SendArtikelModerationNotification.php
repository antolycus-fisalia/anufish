<?php

namespace App\Listeners;

use App\Events\ArtikelModerated;
use App\Models\User;
use App\Notifications\ArtikelModerationNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendArtikelModerationNotification
{
    /**
     * Satu notifikasi per transisi: event dikirim sekali oleh
     * ArticleModerationService setelah commit, jadi tidak ada retry
     * yang bisa menduplikasi notifikasi.
     *
     * Gagal delivery dicatat tanpa membatalkan moderasi yang sudah
     * tersimpan: exception tidak dilempar ulang.
     */
    public function handle(ArtikelModerated $event): void
    {
        try {
            $owner = User::find($event->ownerId);

            if (! $owner) {
                $this->logFailure('article.notification.owner_missing', $event);

                return;
            }

            $owner->notify(new ArtikelModerationNotification(
                $event->action,
                $event->status,
                $event->artikelId,
                $event->title,
            ));
        } catch (Throwable $exception) {
            $this->logFailure('article.notification.delivery_failed', $event, $exception);
        }
    }

    /**
     * Log minimal: hanya aksi dan id, tanpa judul/isi artikel.
     */
    private function logFailure(string $message, ArtikelModerated $event, ?Throwable $exception = null): void
    {
        rescue(fn () => Log::warning($message, [
            'action' => $event->action,
            'actor_id' => $event->actorId,
            'artikel_id' => $event->artikelId,
            'exception' => $exception === null ? null : $exception::class,
        ]), report: false);
    }
}
