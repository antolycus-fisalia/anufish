<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;

class PublicArticleController extends Controller
{
    public function index(): View
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

        return view('pages.article.public.index', [
            'artikel' => $artikel,
        ]);
    }
}
