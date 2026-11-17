<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ArtikelModerationNotification extends Notification
{
    /**
     * @param  string  $action  approve|reject|delete
     */
    public function __construct(
        private string $action,
        private string $status,
        private int $artikelId,
        private string $judul,
    ) {}

    /**
     * Channel disepakati untuk FSH-30: database (in-app) saja,
     * tanpa email/push eksternal.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload minimal: hanya status terbaru dan id artikel milik
     * penerima. Pesan ID/EN diterjemahkan saat render, bukan saat kirim.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'status' => $this->status,
            'artikel_id' => $this->artikelId,
            'judul' => $this->judul,
        ];
    }
}
