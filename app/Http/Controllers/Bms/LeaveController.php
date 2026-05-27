<?php

namespace App\Http\Controllers\Bms;

use App\Models\LeaveRequest;
use App\Models\Staff;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('leave', 'read');
        $items = LeaveRequest::query()->orderByDesc('requested_date')->get();

        return view('leave.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('leave', 'create');

        return view('leave.form', [
            'item' => new LeaveRequest(['requested_date' => now()->toDateString(), 'status' => 'pending']),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('leave', 'create');
        $data = $this->validated($request);
        $staff = Staff::query()->findOrFail($data['staff_id']);
        $data['id'] = $this->nextNumericId(LeaveRequest::class);
        $data['staff_name'] = $staff->name;
        $data['status'] = 'pending';
        $data['requested_date'] = now()->toDateString();
        LeaveRequest::query()->create($data);
        BmsAudit::log('Leave request submitted for '.$staff->name);

        return redirect()->route('bms.leave.index')->with('success', 'Leave request submitted.');
    }

    public function approve(int $id): RedirectResponse
    {
        $this->authorizeBms('leave', 'update');
        $item = LeaveRequest::query()->findOrFail($id);
        $item->update([
            'status' => 'approved',
            'approved_by' => Auth::user()->name,
            'approved_date' => now()->toDateString(),
        ]);
        BmsAudit::log('Approved leave for '.$item->staff_name);

        return redirect()->route('bms.leave.index')->with('success', 'Leave approved.');
    }

    public function reject(int $id): RedirectResponse
    {
        $this->authorizeBms('leave', 'update');
        $item = LeaveRequest::query()->findOrFail($id);
        $item->update([
            'status' => 'rejected',
            'rejected_by' => Auth::user()->name,
            'rejected_date' => now()->toDateString(),
        ]);
        BmsAudit::log('Rejected leave for '.$item->staff_name);

        return redirect()->route('bms.leave.index')->with('success', 'Leave rejected.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'staff_id' => 'required|integer',
            'leave_type' => 'required|string|max:60',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);
    }
}


