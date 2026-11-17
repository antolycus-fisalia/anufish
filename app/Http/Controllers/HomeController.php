<?php

namespace App\Http\Controllers;

use App\Http\Requests\FishQueryRequest;
use App\Models\Artikel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(FishQueryRequest $request): View|RedirectResponse
    {
        $query = $request->validated();

        if ($request->filled('q')) {
            return to_route('fish.index', $query);
        }

        $latestArticle = Artikel::query()
            ->where('status', 'disetujui')
            ->latest('created_at')
            ->orderByDesc('id')
            ->first(['judul', 'kategori']);

        return view('pages.dashboard.index', ['latestArticle' => $latestArticle]);
    }
}
