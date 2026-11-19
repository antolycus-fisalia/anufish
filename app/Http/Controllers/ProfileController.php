<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function show(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $photoUrl = null;
        if ($user->profile_photo_path) {
            $disk = Storage::disk('public');
            $dimensions = @getimagesize($disk->path($user->profile_photo_path));
            if ($dimensions && $dimensions[0] >= 32 && $dimensions[1] >= 32) {
                $photoUrl = $disk->url($user->profile_photo_path);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => [...$user->toArray(), 'photo_url' => $photoUrl]]);
        }

        return view('pages.profile.show', [
            'user' => $user,
            'photoUrl' => $photoUrl,
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return $this->show($request);
        }

        return to_route('profile.show')
            ->with('success', __('Profil berhasil diperbarui.'));
    }
}
