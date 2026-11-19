<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminArtikelListController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $listQuery = $this->listQuery($request);
        $status = $listQuery['status'] ?? 'menunggu';

        $articles = Artikel::query()
            ->with('user:id,name')
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->appends($listQuery);

        if ($request->expectsJson()) {
            return response()->json($articles);
        }

        if ($articles->currentPage() > $articles->lastPage()) {
            return to_route('admin.artikel.index', [...$listQuery, 'page' => $articles->lastPage()]);
        }

        return view('pages.admin.article.index', [
            'articles' => $articles,
            'listQuery' => $listQuery,
            'status' => $status,
        ]);
    }

    public function show(Request $request, Artikel $artikel): View|JsonResponse
    {
        $artikel->load('user:id,name');

        if ($request->expectsJson()) {
            return response()->json(['data' => $artikel->makeVisible('moderation_revision')]);
        }

        return view('pages.admin.article.show', [
            'article' => $artikel,
            'listUrl' => route('admin.artikel.index', $this->listQuery($request)),
            'moderationRevision' => $artikel->moderation_revision,
        ]);
    }

    private function listQuery(Request $request): array
    {
        $validator = Validator::make($request->query(), [
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
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('admin.artikel.index'));
        }

        $query = $validator->validated();

        if (array_key_exists('status', $query)) {
            // Pertahankan status kosong sebagai pilihan "Semua Status" di URL.
            $query['status'] ??= '';
        }

        return $query;
    }
}
