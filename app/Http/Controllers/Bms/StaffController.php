<?php

namespace App\Http\Controllers\Bms;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('staff', 'read');
        $staff = $this->scopeBranch(Staff::query())->orderBy('name')->get();

        return view('staff.index', compact('staff'));
    }

    public function create(): View
    {
        $this->authorizeBms('staff', 'create');

        return view('staff.form', [
            'member' => new Staff(['active' => true]),
            'branches' => $this->branchNames(),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('staff', 'create');

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'role' => 'required|string|max:80',
            'branch' => 'required|string|max:80',
            'salary' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        $data['id'] = $this->nextNumericId(Staff::class);
        $data['active'] = true;
        $data['color'] = '#'.substr(md5($data['email']), 0, 6);

        $staff = Staff::query()->create($data);

        $password = $data['password'] ?? ('QP@'.bin2hex(random_bytes(4)).'1!');
        $user = User::query()->updateOrCreate(
            ['email' => strtolower($data['email'])],
            [
                'name' => $data['name'],
                'password' => Hash::make($password),
                'role' => $data['role'],
                'branch' => $data['branch'],
            ]
        );
        $staff->update(['user_id' => $user->id]);

        return redirect()->route('bms.staff.index')
            ->with('success', 'Staff added.')
            ->with('staff_password', $data['password'] ? null : $password)
            ->with('staff_email', $data['email']);
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('staff', 'update');

        return view('staff.form', [
            'member' => Staff::query()->findOrFail($id),
            'branches' => $this->branchNames(),
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'update');

        $staff = Staff::query()->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'role' => 'required|string|max:80',
            'branch' => 'required|string|max:80',
            'salary' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
        $staff->update($data);

        if ($staff->user_id) {
            User::query()->where('id', $staff->user_id)->update([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'role' => $data['role'],
                'branch' => $data['branch'],
            ]);
        }

        return redirect()->route('bms.staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'delete');
        Staff::query()->findOrFail($id)->delete();

        return redirect()->route('bms.staff.index')->with('success', 'Staff removed.');
    }

    /** @return list<string> */
    private function roles(): array
    {
        return [
            'Receptionist', 'Designer', 'Sales', 'Welder', 'CNC Operator',
            'Laser Operator', 'Fabrication Staff', 'Operations Manager', 'General Manager', 'Admin',
        ];
    }
}


