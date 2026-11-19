<?php

namespace App\Http\Controllers;

use App\Http\Requests\FishQueryRequest;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(FishQueryRequest $request): View|RedirectResponse|JsonResponse
    {
        $query = $request->validated();

        if ($request->filled('q') && ! $request->expectsJson()) {
            return to_route('fish.index', $query);
        }

        $latestArticle = Artikel::query()
            ->where('status', 'disetujui')
            ->latest('created_at')
            ->orderByDesc('id')
            ->first(['id', 'judul', 'kategori']);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['latest_article' => $latestArticle]]);
        }

        return view('pages.dashboard.index', ['latestArticle' => $latestArticle]);
    }
}
