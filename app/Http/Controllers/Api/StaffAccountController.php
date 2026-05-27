<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Support\BmsPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAccountController extends Controller
{
    public function store(Request $request, string $id): JsonResponse
    {
        if (! BmsPermissions::allowed($request->user()?->role, 'staff', 'create')) {
            abort(403, 'You do not have permission to create staff accounts.');
        }

        $staff = Staff::query()->findOrFail($id);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['required', 'string'],
            'role' => ['required', 'string'],
            'branch' => ['nullable', 'string'],
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => strtolower($data['email'])],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'branch' => $data['branch'] ?? $staff->branch ?? 'all',
            ]
        );

        $staff->user_id = $user->id;
        $staff->email = $user->email;
        $staff->save();

        return response()->json(['ok' => true, 'user_id' => $user->id]);
    }
}
