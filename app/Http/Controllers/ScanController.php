<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $path = $file->storeAs('scans', $filename, 'local');

        return response()->json([
            'success' => true,
            'message' => __('Foto berhasil diunggah.'),
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
