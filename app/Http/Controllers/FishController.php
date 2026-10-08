<?php

namespace App\Http\Controllers;

use App\Services\GbifService;
use Illuminate\Http\Request;

class FishController extends Controller
{
    public function search(
        Request $request,
        GbifService $gbif
    ) {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:150',
        ]);

        $result = $gbif->searchSpecies($validated['q']);

        return response()->json(
            $result,
            $result['success'] ? 200 : 502
        );
    }
}
