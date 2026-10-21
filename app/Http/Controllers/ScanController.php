<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanUploadRequest;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ScanController extends Controller
{
    /**
     * Validate, store, and identify the uploaded fish scan image.
     *
     * Pipeline:
     *   1. ScanUploadRequest validates the file (MIME, size, extension).
     *   2. File is saved with a UUID-based name on the private 'local' disk.
     *   3. GeminiService analyses the image and returns candidate species.
     *   4. Structured JSON is returned for the frontend / FSH-15 matcher.
     */
    public function upload(ScanUploadRequest $request, GeminiService $gemini): JsonResponse
    {
        // ── Step 1: Store file safely ────────────────────────────────────────
        $file      = $request->file('fish_image');
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;

        try {
            $path = $file->storeAs('scans', $filename, 'local');
        } catch (Throwable) {
            $path = false;
        }

        if (! is_string($path) || $path === '') {
            return response()->json([
                'success' => false,
                'message' => __('Gagal menyimpan foto ikan.'),
            ], 500);
        }

        // ── Step 2: Identify species via Gemini ──────────────────────────────
        $result = $gemini->identify($path);

        if (! $result['success']) {
            // File is stored but identification failed — clean up and report.
            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? __('Identifikasi ikan gagal.'),
            ], 502);
        }

        // ── Step 3: Return structured result ─────────────────────────────────
        return response()->json([
            'success' => true,
            'message' => __('Foto ikan berhasil diunggah.'),
            'data'    => [
                'path'          => $path,
                'disk'          => 'local',
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'candidates'    => $result['candidates'],
            ],
        ], 200);
    }
}
