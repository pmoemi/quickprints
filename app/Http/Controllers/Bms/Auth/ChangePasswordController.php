<?php

namespace App\Http\Controllers\Bms\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $user = Auth::user();

        // Prevent reusing the same password
        if (Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'Your new password must be different from your current password.']);
        }

        $user->update([
            'password'              => Hash::make($request->input('password')),
            'force_password_change' => false,
        ]);

        return redirect()->route('bms.dashboard')
            ->with('success', 'Password updated successfully. Welcome to QuickPrints BMS!');
    }
}
