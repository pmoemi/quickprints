<?php

namespace App\Http\Controllers\Bms\Auth;

use App\Http\Controllers\Controller;
use App\Support\BmsNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(BmsNavigation::defaultRoute(Auth::user()));
        }

        $settings = app(\App\Services\BmsSettingsService::class)->all();

        return view('auth.login', [
            'settings' => $settings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => strtolower($credentials['email']),
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();
        session(['bms_branch' => $user->branch && $user->branch !== 'all' ? $user->branch : 'all']);

        return redirect()->intended(route(BmsNavigation::defaultRoute($user)));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('bms.login');
    }
}


