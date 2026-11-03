<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminUserEditController extends Controller
{
    public function edit(User $user): JsonResponse
    {
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
    ): JsonResponse {
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
            $user->fill($data);

            if (! $user->save()) {
                throw new \RuntimeException(
                    'User update was not persisted.'
                );
            }
        } catch (Throwable $e) {
            // Jangan mencatat nilai input atau SQL yang
            // berpotensi berisi email pengguna.
            Log::error('admin.user.update_failed', [
                'actor_id' => $request->user()->id,
                'target_id' => $user->id,
                'exception_type' => get_class($e),
            ]);

            return response()->json([
                'message' => __('admin_users.update_failed'),
            ], 500);
        }

        Log::info('admin.user.updated', [
            'actor_id' => $request->user()->id,
            'target_id' => $user->id,
            'changed_fields' => $changedFields,
        ]);

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
