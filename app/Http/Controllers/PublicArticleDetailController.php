<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;

class PublicArticleDetailController extends Controller
{
    public function show(string $artikel): View
    {
        $article = Artikel::query()
            ->with('user:id,name')
            ->where('status', 'disetujui')
            ->whereKey($artikel)
            ->firstOrFail();

        return view('pages.article.public.show', [
            'article' => $article,
        ]);
    }
}
