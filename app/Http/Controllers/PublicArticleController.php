<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicArticleController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $artikel = Artikel::query()
            ->with('user:id,name')
            ->select([
                'id',
                'user_id',
                'judul',
                'kategori',
                'isi',
                'gambar',
                'status',
                'created_at',
            ])
            ->where('status', 'disetujui')
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        if ($request->expectsJson()) {
            return response()->json($artikel);
        }

        return view('pages.article.public.index', [
            'artikel' => $artikel,
        ]);
    }
}
