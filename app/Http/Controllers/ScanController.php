<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanUploadRequest;
use App\Services\RuvicodeService;
use App\Services\ScanReferenceResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Throwable;

class ScanController extends Controller
{
    public function reference(Request $request, ScanReferenceResolver $references): JsonResponse
    {
        $validated = $request->validate([
            'scientific_name' => ['required', 'string', 'max:200'],
        ]);

        return response()->json(['data' => $references->enrich([
            ['scientific_name' => $validated['scientific_name']],
        ])[0]]);
    }

    /**
     * Validate and store the uploaded fish scan image.
     *
     * The file is stored with a UUID-based name so it never depends on
     * the user's original filename. The path is returned so downstream
     * services (Ruvicode, iNaturalist, riwayat) can reference it.
     */
    public function upload(ScanUploadRequest $request, RuvicodeService $identifier): JsonResponse
    {
        $file = $request->file('fish_image');

        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

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

        $result = $identifier->identify($path);
        if (! $result['success']) {
            try {
                Storage::disk('local')->delete($path);
            } catch (FilesystemException) {
                // Cleanup failure must not hide the controlled identification error.
            }

            return response()->json(['success' => false, 'message' => $result['error']], 502);
        }

        return response()->json([
            'success' => true,
            'message' => __('Foto ikan berhasil diunggah.'),
            'data' => [
                'path' => $path,
                'disk' => 'local',
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'candidates' => $result['candidates'],
            ],
        ], 201);
    }
}
