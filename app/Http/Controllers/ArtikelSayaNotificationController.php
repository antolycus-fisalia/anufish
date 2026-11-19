<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\User;
use App\Notifications\ArtikelModerationNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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

        $notifications = $this->moderationNotifications($user)
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get();

        $articles = Artikel::findMany($notifications->pluck('data.artikel_id')->filter()->unique()->all())
            ->keyBy('id');

        return response()->json([
            'data' => $notifications->map(
                fn (DatabaseNotification $notification) => $this->present(
                    $user, $notification, $articles->get($notification->data['artikel_id'] ?? 0),
                )
            )->all(),
            'unread_count' => $this->moderationNotifications($user)->unread()->count(),
        ]);
    }

    public function markRead(Request $request, string $notificationId): JsonResponse
    {
        $user = $request->user();
        $notification = $this->moderationNotifications($user)
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
            'data' => $this->present(
                $user, $notification, Artikel::find((int) ($notification->data['artikel_id'] ?? 0)),
            ),
            'unread_count' => $this->moderationNotifications($user)->unread()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->moderationNotifications($request->user())
            ->unread()
            ->reorder()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => __('article.notification.marked_all'),
        ]);
    }

    private function moderationNotifications(User $user): MorphMany
    {
        return $user->notifications()->where('type', ArtikelModerationNotification::class);
    }

    /**
     * Tautan hanya dibuat bila artikel masih ada dan dimiliki user;
     * artikel yang sudah dihapus tidak lagi menautkan ke mana pun.
     */
    private function present(User $user, DatabaseNotification $notification, ?Artikel $artikel): array
    {
        $payload = (array) $notification->data;

        $link = null;

        if ($artikel && $user->can('update', $artikel)) {
            $link = route(
                request()->routeIs('api.v1.*') ? 'api.v1.articles.show' : 'artikel-saya.edit',
                ['artikel' => $artikel->getKey()],
            );
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
