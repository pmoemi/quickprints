<?php

namespace App\Http\Controllers\Bms;

use App\Models\BmsNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function poll(Request $request): JsonResponse
    {
        $user = Auth::user();
        $since = max(0, (int) $request->query('since', 0));

        $query = BmsNotification::query()
            ->where('user_id', $user->id)
            ->orderBy('id');

        if ($since > 0) {
            $query->where('id', '>', $since);
        } else {
            $query->whereNull('read_at')->limit(20);
        }

        $items = $query->get(['id', 'title', 'body', 'type', 'job_id', 'created_at', 'read_at']);
        $latestId = (int) (BmsNotification::query()->where('user_id', $user->id)->max('id') ?? 0);
        $unread = BmsNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'latest_id' => $latestId,
            'unread' => $unread,
            'items' => $items,
        ]);
    }
}


