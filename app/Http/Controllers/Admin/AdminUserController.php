<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    private const PER_PAGE = 10;

    private const USER_FIELDS = [
        'id',
        'name',
        'email',
        'role',
        'status',
        'created_at',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $validated = Validator::make($request->query(), [
            'search' => ['sometimes', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ])->validate();

        $search = trim($validated['search'] ?? '');

        $users = User::query()
            ->select(self::USER_FIELDS)
            ->when($search !== '', function ($query) use ($search) {
                // Escape wildcard LIKE agar % dan _
                // tidak menjadi pola pencarian.
                $term = '%'.strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';

                $query->where(function ($query) use ($term) {
                    $query->whereRaw("name LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("email LIKE ? ESCAPE '!'", [$term]);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('pages.admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(
        Request $request,
        User $user
    ): View|JsonResponse {
        $data = $user->only(self::USER_FIELDS);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data,
            ]);
        }

        return view('pages.admin.users.show', [
            'user' => $user,
        ]);
    }
}
