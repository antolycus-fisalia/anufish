<?php

namespace App\Http\Controllers;

use App\Http\Requests\FishQueryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(FishQueryRequest $request): View|RedirectResponse
    {
        $query = $request->validated();

        if ($request->filled('q')) {
            return to_route('fish.index', $query);
        }

        return view('pages.dashboard.index');
    }
}
