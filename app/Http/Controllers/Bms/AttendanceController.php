<?php

namespace App\Http\Controllers\Bms;

use App\Models\AttendanceRecord;
use App\Models\Staff;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('attendance', 'read');
        $date = $request->get('date', now()->toDateString());
        $records = AttendanceRecord::query()->whereDate('date', $date)->orderBy('staff_name')->get();
        $staff = Staff::query()->where('active', true)->orderBy('name')->get();

        return view('attendance.index', compact('records', 'staff', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('attendance', 'create');
        $data = $request->validate([
            'staff_id' => 'required|integer',
            'date' => 'required|date',
            'check_in' => 'nullable|string|max:10',
            'check_out' => 'nullable|string|max:10',
            'status' => 'nullable|string|max:40',
        ]);

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $hours = 0;
        if (! empty($data['check_in']) && ! empty($data['check_out'])) {
            $in = strtotime($data['check_in']);
            $out = strtotime($data['check_out']);
            if ($out > $in) {
                $hours = round(($out - $in) / 3600, 2);
            }
        }

        $payload = [
            'staff_name' => $staff->name,
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'hours' => $hours,
            'status' => $data['status'] ?? 'Present',
            'source' => 'Manual',
        ];

        $record = AttendanceRecord::query()
            ->where('staff_id', $data['staff_id'])
            ->whereDate('date', $data['date'])
            ->first();

        if ($record) {
            $record->update($payload);
        } else {
            $payload['id'] = $this->nextNumericId(AttendanceRecord::class);
            $payload['staff_id'] = $data['staff_id'];
            $payload['date'] = $data['date'];
            AttendanceRecord::query()->create($payload);
        }

        BmsAudit::log('Recorded attendance for '.$staff->name);

        return redirect()->route('bms.attendance.index', ['date' => $data['date']])->with('success', 'Attendance saved.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('attendance', 'delete');
        $rec = AttendanceRecord::query()->findOrFail($id);
        $date = $rec->date?->toDateString();
        $rec->delete();
        BmsAudit::log('Deleted attendance record');

        return redirect()->route('bms.attendance.index', ['date' => $date])->with('success', 'Record removed.');
    }
}


