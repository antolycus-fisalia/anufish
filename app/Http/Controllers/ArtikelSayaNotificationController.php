<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class ArtikelSayaNotificationController extends Controller
{
    private const LIMIT = 20;

    /**
     * Notifikasi hanya untuk pemilik artikel: dibaca dari relasi
     * notifiable milik user yang sedang login, bukan query global.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->present($user, $notification))
            ->all();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notificationId)
            ->first();

        if (! $notification instanceof DatabaseNotification) {
            return response()->json([
                'message' => __('article.notification.not_found'),
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => __('article.notification.marked'),
            'data' => $this->present($request->user(), $notification->refresh()),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markRead();

        return response()->json([
            'message' => __('article.notification.marked_all'),
        ]);
    }

    /**
     * Tautan hanya dibuat bila artikel masih ada dan dimiliki user;
     * artikel yang sudah dihapus tidak lagi menautkan ke mana pun.
     */
    private function present(User $user, DatabaseNotification $notification): array
    {
        $payload = (array) $notification->data;
        $artikel = Artikel::find((int) ($payload['artikel_id'] ?? 0));

        $link = null;

        if ($artikel && $user->can('update', $artikel)) {
            $link = route('artikel-saya.edit', ['artikel' => $artikel->getKey()]);
        }

        return [
            'id' => $notification->id,
            'action' => (string) ($payload['action'] ?? ''),
            'status' => (string) ($payload['status'] ?? ''),
            'judul' => (string) ($payload['judul'] ?? ''),
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'link' => $link,
        ];
    }
}
