<?php

namespace App\Http\Controllers;

use App\Models\RiwayatScan;
use App\Services\ScanReferenceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ScanHistoryController extends Controller
{
    private function owned(Request $request, RiwayatScan $history): void
    {
        abort_unless($history->user_id === $request->user()->id, 404);
    }

    public function index(Request $request)
    {
        $histories = RiwayatScan::where('user_id', $request->user()->id)->latest()->orderByDesc('id')->paginate(12);

        return view('pages.scan.history.index', compact('histories'));
    }

    public function show(Request $request, RiwayatScan $history)
    {
        $this->owned($request, $history);

        return view('pages.scan.history.show', compact('history'));
    }

    public function image(Request $request, RiwayatScan $history)
    {
        $this->owned($request, $history);
        abort_unless(Storage::disk('local')->exists($history->image_path), 404);

        return Storage::disk('local')->response($history->image_path, null, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reference(Request $request, RiwayatScan $history, ScanReferenceResolver $resolver)
    {
        $this->owned($request, $history);
        $validated = $request->validate(['candidate_index' => ['required', 'integer', 'min:0', 'max:'.(count($history->candidates) - 1)]]);
        $index = (int) $validated['candidate_index'];
        $candidate = $history->candidates[$index];
        $resolved = $resolver->enrich([$candidate])[0];
        try {
            $history->selected_candidate_index = $index;
            $history->scientific_name = $candidate['scientific_name'];
            $history->confidence = $candidate['confidence'] ?? null;
            $history->match_status = $resolved['match_status'];
            $history->inaturalist_taxon_id = $resolved['match_status'] === 'matched' ? $resolved['taxon_id'] : null;
            if (! $history->save()) {
                throw new RuntimeException('Scan reference was not persisted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => __('history.save_failed')], 500);
        }

        return response()->json(['data' => $resolved]);
    }
}
