<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanUploadRequest;
use App\Models\RiwayatScan;
use App\Services\RuvicodeService;
use App\Services\ScanHistoryService;
use App\Services\ScanReferenceResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
    public function upload(ScanUploadRequest $request, RuvicodeService $identifier, ScanHistoryService $history): JsonResponse
    {
        $operationId = $request->validated('operation_id') ?? (string) Str::uuid();
        $lock = Cache::lock('scan-upload:'.$request->user()->id.':'.$operationId, 180);
        if (! $lock->get()) {
            return response()->json(['success' => false, 'message' => __('history.processing')], 409);
        }
        try {
            $hash = hash_file('sha256', $request->file('fish_image')->getRealPath());
            $existing = RiwayatScan::where('user_id', $request->user()->id)->where('operation_id', $operationId)->first();
            if ($existing) {
                if (! hash_equals($existing->image_hash, $hash)) {
                    return response()->json(['success' => false, 'message' => __('history.conflict')], 409);
                }

                return $this->uploaded($request, $existing->image_path, $existing->candidates, $existing);
            }

            return $this->identifyAndSave($request, $identifier, $history, $operationId, $hash);
        } finally {
            $lock->release();
        }
    }

    private function identifyAndSave(ScanUploadRequest $request, RuvicodeService $identifier, ScanHistoryService $history, string $operationId, string $hash): JsonResponse
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

        try {
            $result = $identifier->identify($path);
        } catch (Throwable $exception) {
            $history->cleanup($path);
            report($exception);

            return response()->json(['success' => false, 'message' => __('history.identification_failed')], 502);
        }
        if (! $result['success']) {
            $history->cleanup($path);

            return response()->json(['success' => false, 'message' => $result['error']], 502);
        }

        $record = null;
        if ($result['candidates'] !== []) {
            try {
                $record = $history->save($request->user()->id, $operationId, $hash, $path, $result['candidates']);
            } catch (Throwable $exception) {
                $history->cleanup($path);
                report($exception);

                return response()->json(['success' => false, 'message' => __('history.save_failed')], 500);
            }
        } else {
            $history->cleanup($path);
        }

        return $this->uploaded($request, $path, $result['candidates'], $record);
    }

    private function uploaded(ScanUploadRequest $request, string $path, array $candidates, ?RiwayatScan $record): JsonResponse
    {
        $file = $request->file('fish_image');

        return response()->json([
            'success' => true,
            'message' => $record ? __('Foto ikan berhasil diunggah.') : __('history.unknown'),
            'data' => [
                'path' => $record ? $path : null,
                'disk' => $record ? 'local' : null,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'candidates' => $candidates,
                'history_id' => $record?->id,
                'history_url' => $record ? route('scan.history.show', $record) : null,
            ],
        ], 201);
    }
}
