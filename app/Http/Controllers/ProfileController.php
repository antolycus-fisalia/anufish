<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProfileController extends Controller
{
    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('update', $user);

        $data = $request->validated();

        unset($data['profile_photo']);

        $newPhotoPath = null;
        $oldPhotoPath = $user->profile_photo_path;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request
                ->file('profile_photo')
                ->store('profile-photos', 'public');

            $data['profile_photo_path'] = $newPhotoPath;
        }

        try {
            $user->update($data);
        } catch (Throwable $e) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $e;
        }

        if ($newPhotoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return back()->with(
            'success',
            'Profil berhasil diperbarui.'
        );
    }
}
