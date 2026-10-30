<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ArtikelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $artikel = Artikel::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($artikel);
    }

    public function store(ArticleRequest $request): JsonResponse
    {
        $data = $request->safe()->only([
            'judul',
            'kategori',
            'isi',
        ]);

        // Sementara hanya menerima teks biasa.
        // Sanitasi rich text final ditangani FSH-22.
        $data['isi'] = strip_tags($data['isi']);

        $path = null;

        try {
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')
                    ->store('artikel-images', 'public');

                if (! $path) {
                    throw new \RuntimeException(
                        'Gagal menyimpan gambar artikel.'
                    );
                }

                $data['gambar'] = $path;
            }

            $artikel = new Artikel($data);

            $artikel->user_id = $request->user()->id;
            $artikel->status = 'menunggu';

            $artikel->save();

            return response()->json([
                'message' => 'Artikel berhasil dibuat.',
                'data' => $artikel,
            ], 201);

        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            report($e);

            return response()->json([
                'message' => 'Gagal menyimpan artikel.',
            ], 500);
        }
    }

    public function show(
        Request $request,
        Artikel $artikel
    ): JsonResponse {
        abort_unless(
            $artikel->user_id === $request->user()->id,
            403
        );

        return response()->json([
            'data' => $artikel,
        ]);
    }

    public function update(
        ArticleRequest $request,
        Artikel $artikel
    ): JsonResponse {
        abort_unless(
            $artikel->user_id === $request->user()->id,
            403
        );

        $data = $request->safe()->only([
            'judul',
            'kategori',
            'isi',
        ]);

        $data['isi'] = strip_tags($data['isi']);

        $oldPath = $artikel->gambar;
        $newPath = null;

        try {
            if ($request->hasFile('gambar')) {
                $newPath = $request->file('gambar')
                    ->store('artikel-images', 'public');

                if (! $newPath) {
                    throw new \RuntimeException(
                        'Gagal menyimpan gambar artikel.'
                    );
                }

                $data['gambar'] = $newPath;
            }

            $artikel->fill($data);

            // Setiap perubahan konten kembali menunggu moderasi.
            $artikel->status = 'menunggu';

            $artikel->save();

        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            report($e);

            return response()->json([
                'message' => 'Gagal memperbarui artikel.',
            ], 500);
        }

        // Hapus gambar lama hanya setelah DB berhasil disimpan.
        if ($newPath && $oldPath) {
            try {
                Storage::disk('public')->delete($oldPath);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => 'Artikel berhasil diperbarui.',
            'data' => $artikel->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Artikel $artikel
    ): JsonResponse {
        abort_unless(
            $artikel->user_id === $request->user()->id,
            403
        );

        $path = $artikel->gambar;

        try {
            $artikel->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal menghapus artikel.',
            ], 500);
        }

        if ($path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => 'Artikel berhasil dihapus.',
        ]);
    }
}
