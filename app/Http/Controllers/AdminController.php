<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function index(): View
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

        return view('pages.admin.dashboard', [
            'articleCounts' => $articleCounts,
            'pendingArticles' => $pendingArticles,
            'userCounts' => $userCounts,
        ]);
    }
}
