<?php

namespace App\Http\Controllers;

use App\Http\Requests\FishPhotosRequest;
use App\Http\Requests\FishQueryRequest;
use App\Services\FishDetailService;
use App\Services\InaturalistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FishController extends Controller
{
    public function photos(FishPhotosRequest $request, string $taxonId, FishDetailService $details): JsonResponse
    {
        $before = $request->validated()['before'] ?? null;
        $result = $details->photos((int) $taxonId, $before !== null ? (int) $before : null);

        return response()->json($result, $result['status']);
    }

    public function extras(string $taxonId, FishDetailService $details): JsonResponse
    {
        $result = $details->enrich((int) $taxonId);

        return response()->json($result, $result['status']);
    }

    public function show(FishQueryRequest $request, string $taxonId, FishDetailService $details): Response
    {
        $validated = $request->validated();
        $result = $details->find((int) $taxonId);
        if ($result['fish'] && isset($validated['q'])) {
            $needle = mb_convert_case(trim($validated['q']), MB_CASE_FOLD, 'UTF-8');
            foreach ($result['fish']['vernacular_names'] as $name) {
                if (mb_convert_case($name['name'], MB_CASE_FOLD, 'UTF-8') === $needle) {
                    $result['fish']['common_name'] = $name['name'];
                    break;
                }
            }
        }
        $returnParameters = isset($validated['q']) && $validated['q'] !== ''
            ? ['q' => $validated['q'], 'page' => (int) ($validated['page'] ?? 1)] : [];

        return response()->view('pages.fish.show', [
            'fish' => $result['fish'],
            'status' => $result['status'],
            'returnUrl' => route('fish.index', $returnParameters),
            'returnLabel' => $returnParameters ? __('Kembali ke hasil pencarian') : __('Kembali ke Jelajahi Ikan'),
        ], $result['status']);
    }

    public function index(FishQueryRequest $request, InaturalistService $inaturalist): View
    {
        $validated = $request->validated();

        $result = isset($validated['q']) && $validated['q'] !== ''
            ? $inaturalist->searchSpecies($validated['q'], 20, (int) ($validated['page'] ?? 1))
            : ['success' => true, 'data' => [], 'message' => null];

        return view('pages.fish.index', [
            'fishes' => $result['data'],
            'error' => $result['success'] ? null : $result['message'],
            'pagination' => $result['pagination'] ?? null,
        ]);
    }

    public function search(
        FishQueryRequest $request,
        InaturalistService $inaturalist
    ): JsonResponse {
        $validated = $request->validated();

        $result = $inaturalist->searchSpecies($validated['q'], 20, (int) ($validated['page'] ?? 1));

        return response()->json(
            $result,
            $result['status'] ?? 200
        );
    }
}
