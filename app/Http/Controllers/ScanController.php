<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Throwable;

class ScanController extends Controller
{
    /**
     * Validate and store the uploaded fish scan image.
     *
     * The file is stored with a UUID-based name so it never depends on
     * the user's original filename. The path is returned so downstream
     * services (Gemini AI, iNaturalist, riwayat) can reference it.
     */
    public function upload(ScanUploadRequest $request): JsonResponse
    {
        $file = $request->file('fish_image');

        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

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

        return response()->json([
            'success' => true,
            'message' => __('Foto ikan berhasil diunggah.'),
            'data'    => [
                'path'          => $path,
                'disk'          => 'local',
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
            ],
        ], 201);
    }
}
