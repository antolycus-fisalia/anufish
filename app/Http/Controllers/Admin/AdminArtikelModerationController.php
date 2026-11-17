<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Services\ArticleModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminArtikelModerationController extends Controller
{
    public function approve(Request $request, Artikel $artikel): JsonResponse
    {
        Gate::authorize('moderate', $artikel);

        return $this->respond(
            app(ArticleModerationService::class)->approve($request->user(), $artikel, $this->revision($request))
        );
    }

    public function reject(Request $request, Artikel $artikel): JsonResponse
    {
        Gate::authorize('moderate', $artikel);

        return $this->respond(
            app(ArticleModerationService::class)->reject($request->user(), $artikel, $this->revision($request))
        );
    }

    public function delete(Request $request, Artikel $artikel): JsonResponse
    {
        Gate::authorize('moderate', $artikel);

        return $this->respond(
            app(ArticleModerationService::class)->deleteArticle($request->user(), $artikel, $this->revision($request))
        );
    }

    private function revision(Request $request): int
    {
        return (int) $request->validate(['moderation_revision' => ['required', 'integer', 'min:1']])['moderation_revision'];
    }

    private function respond(array $result): JsonResponse
    {
        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        }

        $code = $result['status'] === 'conflict' ? 409 : 500;

        return response()->json(['message' => $result['message']], $code);
    }
}
