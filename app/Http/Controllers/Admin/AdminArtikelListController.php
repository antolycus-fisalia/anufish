<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminArtikelListController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $validated = Validator::make($request->query(), [
            'status' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in([
                    'menunggu',
                    'disetujui',
                    'ditolak',
                ]),
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ])->validate();

        // Tanpa parameter status: tampilkan artikel menunggu.
        // Parameter status kosong: tampilkan semua artikel.
        $status = array_key_exists('status', $validated)
            ? ($validated['status'] ?? '')
            : 'menunggu';

        $articles = Artikel::query()
            ->with('user:id,name')
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.article.index', [
            'articles' => $articles,
            'status' => $status,
        ]);
    }

    public function show(Artikel $artikel): View
    {
        $artikel->load('user:id,name');

        return view('pages.admin.article.show', [
            'article' => $artikel,
            'moderationRevision' => $artikel->moderation_revision,
        ]);
    }
}
