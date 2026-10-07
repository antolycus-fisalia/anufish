<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
//    public function show(Request $request): View
//    {
//        return view('pages.profile.show', [
//            'user' => $request->user(),
//        ]);
//    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->safe()->except('profile_photo');

        $oldPhotoPath = $user->profile_photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request
                ->file('profile_photo')
                ->store('profile-photos', 'public');

            $data['profile_photo_path'] = $newPhotoPath;
        }

        try {
            $user->update($data);
        } catch (Throwable $exception) {
            if ($newPhotoPath !== null) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath !== null && $oldPhotoPath !== null) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return to_route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
