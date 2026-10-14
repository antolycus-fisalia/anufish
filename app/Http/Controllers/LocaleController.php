<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(LocaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $request->session()->put('locale', $data['locale']);
        $returnTo = $data['return_to'] ?? '/';
        if (! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') || preg_match('/[\\\\\r\n]/', $returnTo)) {
            $returnTo = '/';
        }

        return redirect($returnTo);
    }
}
