<?php

namespace App\Http\Controllers;

use App\Services\GbifService;
use Illuminate\Http\Request;

class FishController extends Controller
{
    public function index(Request $request, GbifService $gbif)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|min:2|max:150',
            'page' => 'nullable|integer|min:1|max:5000',
        ]);

        $result = !empty($validated['q'])
            ? $gbif->searchSpecies($validated['q'], 20, (int) ($validated['page'] ?? 1))
            : ['success' => true, 'data' => [], 'message' => null];

        return view('pages.dashboard.index', [
            'fishes' => $result['data'],
            'error' => $result['success'] ? null : $result['message'],
            'pagination' => $result['pagination'] ?? null,
        ]);
    }

    public function search(
        Request $request,
        GbifService $gbif
    ) {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:150',
            'page' => 'nullable|integer|min:1|max:5000',
        ]);

        $result = $gbif->searchSpecies($validated['q'], 20, (int) ($validated['page'] ?? 1));

        return response()->json(
            $result,
            $result['success'] ? 200 : 502
        );
    }
}
