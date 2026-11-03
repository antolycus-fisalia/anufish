<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
        $data['isi'] = trim(strip_tags($data['isi']));

        if ($data['isi'] === '') {
            throw ValidationException::withMessages([
                'isi' => __('article.validation.content_required'),
            ]);
        }

        $path = null;

        try {
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')
                    ->store('artikel-images', 'public');

                if (! $path) {
                    throw new \RuntimeException(
                        __('article.messages.create_failed')
                    );
                }

                $data['gambar'] = $path;
            }

            $artikel = new Artikel($data);
            $artikel->user_id = $request->user()->id;
            $artikel->status = 'menunggu';
            if (! $artikel->save()) {
                throw new \RuntimeException('Article was not persisted.');
            }

            return response()->json([
                'message' => __('article.messages.created'),
                'data' => $artikel,
            ], 201);
        } catch (Throwable $e) {
            report($e);

            if ($path) {
                $this->cleanupFile($path);
            }

            return response()->json([
                'message' => __('article.messages.create_failed'),
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

        $data['isi'] = trim(strip_tags($data['isi']));

        if ($data['isi'] === '') {
            throw ValidationException::withMessages([
                'isi' => __('article.validation.content_required'),
            ]);
        }

        $oldPath = $artikel->gambar;
        $newPath = null;

        try {
            if ($request->hasFile('gambar')) {
                $newPath = $request->file('gambar')
                    ->store('artikel-images', 'public');

                if (! $newPath) {
                    throw new \RuntimeException(
                        __('article.messages.update_failed')
                    );
                }

                $data['gambar'] = $newPath;
            }

            $artikel->fill($data);

            // Perubahan artikel kembali menunggu moderasi.
            $artikel->status = 'menunggu';
            if (! $artikel->save()) {
                throw new \RuntimeException('Article was not persisted.');
            }
        } catch (Throwable $e) {
            report($e);

            if ($newPath) {
                $this->cleanupFile($newPath);
            }

            return response()->json([
                'message' => __('article.messages.update_failed'),
            ], 500);
        }

        // Gambar lama dihapus setelah database berhasil diperbarui.
        if ($newPath && $oldPath && $newPath !== $oldPath) {
            $this->cleanupFile($oldPath);
        }

        return response()->json([
            'message' => __('article.messages.updated'),
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
            if (! $artikel->delete()) {
                throw new \RuntimeException('Article was not deleted.');
            }
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('article.messages.delete_failed'),
            ], 500);
        }

        // Hapus gambar setelah record berhasil dihapus.
        if ($path) {
            $this->cleanupFile($path);
        }

        return response()->json([
            'message' => __('article.messages.deleted'),
        ]);
    }

    /**
     * Cleanup file tanpa menutupi exception utama.
     */
    private function cleanupFile(string $path): void
    {
        try {
            $deleted = Storage::disk('public')->delete($path);

            if (! $deleted) {
                report(new \RuntimeException(
                    'Article file cleanup failed.'
                ));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
