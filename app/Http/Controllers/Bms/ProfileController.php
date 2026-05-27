<?php

namespace App\Http\Controllers\Bms;

use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends BmsController
{
    public function show(): View
    {
        $user = Auth::user();
        $staff = Staff::query()->where('user_id', $user->id)->first();

        return view('profile.edit', compact('user', 'staff'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => strtolower($data['email']),
        ]);

        // Sync linked staff record
        $staff = Staff::query()->where('user_id', $user->id)->first();
        if ($staff) {
            $staff->update(array_filter([
                'name'  => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? $staff->phone,
            ]));
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', 'Password changed successfully.');
    }
}
