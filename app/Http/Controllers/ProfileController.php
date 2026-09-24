<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $user->setAttribute(
            'scan_count',
            $user->riwayatScan()->count()
        );

        $user->setAttribute(
            'article_count',
            $user->artikel()->count()
        );

        $user->setAttribute(
            'approved_count',
            $user->artikel()
                ->where('status', 'disetujui')
                ->count()
        );

        return view('profile.index', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
