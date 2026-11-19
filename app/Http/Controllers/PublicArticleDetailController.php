<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicArticleDetailController extends Controller
{
    public function show(Request $request, string $artikel): View|JsonResponse
    {
        $article = Artikel::query()
            ->with('user:id,name')
            ->where('status', 'disetujui')
            ->whereKey($artikel)
            ->firstOrFail();

        if ($request->expectsJson()) {
            return response()->json(['data' => $article]);
        }

        return view('pages.article.public.show', [
            'article' => $article,
            'listPage' => filter_var($request->query('page'), FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 2],
            ]) ?: null,
        ]);
    }
}
