<?php

namespace App\Http\Controllers\Bms;

use App\Models\BmsMessage;
use App\Models\User;
use App\Support\BmsAudit;
use App\Support\BmsNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends BmsController
{
    public function index(): View
    {
        $user = Auth::user();
        $inbox = BmsMessage::query()
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                    ->orWhere('to_role', $user->role);
            })
            ->orderByDesc('created_at')
            ->get();
        $sent = BmsMessage::query()->where('from_user_id', $user->id)->orderByDesc('created_at')->limit(20)->get();

        return view('messages.index', compact('inbox', 'sent'));
    }

    public function create(): View
    {
        return view('messages.form', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => array_keys(BmsNavigation::rolePages()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to_user_id' => 'nullable|integer|exists:users,id',
            'to_role' => 'nullable|string|max:60',
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ]);

        if (empty($data['to_user_id']) && empty($data['to_role'])) {
            return back()->withErrors(['to_user_id' => 'Select a recipient or role.'])->withInput();
        }

        $user = $request->user();
        BmsMessage::query()->create([
            'id' => $this->nextNumericId(BmsMessage::class),
            'from_user_id' => $user->id,
            'from_name' => $user->name,
            'to_user_id' => $data['to_user_id'] ?? null,
            'to_role' => $data['to_role'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);
        BmsAudit::log('Sent message: '.$data['subject']);

        return redirect()->route('bms.messages.index')->with('success', 'Message sent.');
    }

    public function show(int $id): View
    {
        $msg = BmsMessage::query()->findOrFail($id);
        $user = Auth::user();
        if ($msg->to_user_id === $user->id || $msg->to_role === $user->role || $msg->from_user_id === $user->id) {
            if (! $msg->read_at && ($msg->to_user_id === $user->id || $msg->to_role === $user->role)) {
                $msg->update(['read_at' => now()]);
            }
        } else {
            abort(403);
        }

        return view('messages.show', compact('msg'));
    }
}


