<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminUserEditController extends Controller
{
    public function edit(Request $request, User $user): JsonResponse|View
    {
        if (! $request->expectsJson()) {
            return view('pages.admin.users.edit', compact('user'));
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(
        AdminUpdateUserRequest $request,
        User $user
    ): JsonResponse|RedirectResponse {
        $data = $request->safe()->only([
            'name',
            'email',
        ]);

        $changedFields = [];

        if ($user->name !== $data['name']) {
            $changedFields[] = 'name';
        }

        if ($user->email !== $data['email']) {
            $changedFields[] = 'email';
        }

        try {
            DB::transaction(function () use ($user, $data) {
                $user->fill($data);
                if (! $user->save()) {
                    throw new \RuntimeException('User update was not persisted.');
                }
            });
        } catch (Throwable $e) {
            // Jangan mencatat nilai input atau SQL yang
            // berpotensi berisi email pengguna.
            Log::error('admin.user.update_failed', [
                'actor_id' => $request->user()->id,
                'target_id' => $user->id,
                'exception_type' => get_class($e),
            ]);

            if (! $request->expectsJson()) {
                return back()->withInput($data)->withErrors(['update' => __('admin_users.update_failed')]);
            }

            return response()->json([
                'message' => __('admin_users.update_failed'),
            ], 500);
        }

        Log::info('admin.user.updated', [
            'actor_id' => $request->user()->id,
            'target_id' => $user->id,
            'changed_fields' => $changedFields,
        ]);

        if (! $request->expectsJson()) {
            return to_route('admin.users.edit', $user)->with('success', __('admin_users.updated'));
        }

        return response()->json([
            'message' => __('admin_users.updated'),
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
