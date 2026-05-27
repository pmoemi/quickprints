<?php

namespace App\Http\Controllers\Bms;

use App\Models\BmsNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends BmsController
{
    public function index(): View
    {
        $user = Auth::user();
        $items = BmsNotification::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('notifications.index', compact('items'));
    }

    public function markRead(int $id): RedirectResponse
    {
        $n = BmsNotification::query()->findOrFail($id);
        if ($n->user_id && $n->user_id !== Auth::id()) {
            abort(403);
        }
        $n->update(['read_at' => now()]);

        return redirect()->route('bms.notifications.index');
    }

    public function markAllRead(): RedirectResponse
    {
        BmsNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->route('bms.notifications.index')->with('success', 'All marked read.');
    }
}


