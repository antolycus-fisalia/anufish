<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $articleCounts = Artikel::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingArticles = Artikel::query()
            ->with('user:id,name')
            ->where('status', 'menunggu')
            ->latest()
            ->orderByDesc('id')
            ->limit(3)
            ->get(['id', 'judul', 'user_id', 'created_at']);

        $userCounts = User::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'article_counts' => $articleCounts,
                'pending_articles' => $pendingArticles,
                'user_counts' => $userCounts,
            ]]);
        }

        return view('pages.admin.dashboard', [
            'articleCounts' => $articleCounts,
            'pendingArticles' => $pendingArticles,
            'userCounts' => $userCounts,
        ]);
    }
}
