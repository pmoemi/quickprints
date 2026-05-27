<?php

namespace App\Http\Controllers\Bms\Auth;

use App\Http\Controllers\Controller;
use App\Services\BmsMailer;
use App\Services\BmsSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        $settings = app(BmsSettingsService::class)->all();

        return view('auth.forgot-password', compact('settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $settings = app(BmsSettingsService::class)->all();
        BmsMailer::configure($settings);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Password reset link sent! Check your email.')
            : back()->withErrors(['email' => __($status)]);
    }
}
