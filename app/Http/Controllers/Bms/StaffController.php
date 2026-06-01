<?php

namespace App\Http\Controllers\Bms;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Support\BmsPermissions;
use App\Support\BmsSettingsDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('staff', 'read');

        // Branch filter is local to this page (not the global session branch).
        if ($this->canViewAllBranches()) {
            $branch = request()->has('branch') ? (string) request('branch', 'all') : 'all';
        } else {
            $branch = $this->userBranch() ?? 'all';
        }
        $q = trim((string) request('q', ''));

        $query = Staff::query()->orderBy('name');

        if ($branch !== 'all') {
            $query->where(function ($sq) use ($branch) {
                $sq->where('branch', $branch)->orWhere('branch', 'all');
            });
        }
        if ($q !== '') {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('role', 'like', "%{$q}%");
            });
        }

        $staff = $query->get();

        $branches = array_merge(['all'], $this->branchNames());

        return view('staff.index', compact('staff', 'branch', 'branches', 'q'));
    }

    public function create(): View
    {
        $this->authorizeBms('staff', 'create');

        return view('staff.form', [
            'member' => new Staff([
                'active'      => true,
                'is_designer' => false,
                'name'        => request('name'),
                'email'       => request('email'),
                'role'        => request('role'),
                'branch'      => request('branch', 'all'),
            ]),
            'branches' => $this->branchNames(),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('staff', 'create');

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:staff,email|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'role'     => 'required|string|max:80',
            'branch'   => 'required|string|max:80',
            'salary'   => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8',
            'is_designer' => 'nullable|boolean',
        ]);

        $data['id'] = $this->nextNumericId(Staff::class);
        $data['active'] = true;
        $data['is_designer'] = $request->boolean('is_designer');
        $data['salary'] = $data['salary'] ?? 0;
        $data['color'] = '#'.substr(md5($data['email']), 0, 6);

        $password = $data['password'] ?? ('QP@'.bin2hex(random_bytes(4)).'1!');
        unset($data['password']); // staff table has no password column

        $staff = Staff::query()->create($data);

        $existingUser = User::query()->where('email', strtolower($data['email']))->first();

        if ($existingUser) {
            // Never overwrite an Admin user's role, name or password via staff creation
            if ($existingUser->role === 'Admin') {
                $staff->update(['user_id' => $existingUser->id]);
                $showPassword = null; // Password unchanged — don't display one
                return redirect()->route('bms.staff.index')
                    ->with('success', 'Staff linked to existing Admin account. Password and role were not changed.');
            }
            // Existing non-admin user: update name/role/branch but NOT password unless one was explicitly provided
            $updates = ['name' => $data['name'], 'role' => $data['role'], 'branch' => $data['branch']];
            if ($request->filled('password')) {
                $updates['password'] = Hash::make($password);
            }
            $existingUser->update($updates);
            $user = $existingUser;
            $showPassword = $request->filled('password') ? null : null; // existing account — no new password to show
        } else {
            // Always require new staff to change their password on first login
            $user = User::query()->create([
                'name'                  => $data['name'],
                'email'                 => strtolower($data['email']),
                'password'              => Hash::make($password),
                'role'                  => $data['role'],
                'branch'                => $data['branch'],
                'force_password_change' => true,
            ]);
            $showPassword = $request->filled('password') ? null : $password;
        }

        $staff->update(['user_id' => $user->id]);

        return redirect()->route('bms.staff.index')
            ->with('success', 'Staff added.')
            ->with('staff_password', $showPassword)
            ->with('staff_email', $data['email']);
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('staff', 'update');
        $member = Staff::query()->findOrFail($id);

        // Linked user — check if they're Admin
        $linkedUser = $member->user_id ? User::query()->find($member->user_id) : null;
        $isAdminUser = $linkedUser?->role === 'Admin';

        return view('staff.form', [
            'member'      => $member,
            'branches'    => $this->branchNames(),
            'roles'       => $this->roles(),
            'isAdminUser' => $isAdminUser,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'update');

        $staff = Staff::query()->findOrFail($id);
        $linkedUserId = $staff->user_id;

        $data = $request->validate([
            'name'   => 'required|string|max:120',
            'email'  => [
                'required', 'email',
                \Illuminate\Validation\Rule::unique('staff', 'email')->ignore($staff->id),
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($linkedUserId),
            ],
            'phone'  => 'nullable|string|max:30',
            'role'   => 'required|string|max:80',
            'branch' => 'required|string|max:80',
            'salary' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'is_designer' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
        $data['is_designer'] = $request->boolean('is_designer');
        $data['salary'] = $data['salary'] ?? 0;
        $staff->update($data);

        if ($staff->user_id) {
            $linkedUser = User::query()->find($staff->user_id);

            // Never silently overwrite the Admin role via staff form
            $newRole = $data['role'];
            if ($linkedUser?->role === 'Admin' && $newRole !== 'Admin') {
                // Only allow demotion if the current user is themselves an Admin
                if (auth()->user()?->role !== 'Admin') {
                    $newRole = 'Admin'; // Restore — non-admin cannot demote Admin
                }
            }

            $linkedUser?->update([
                'name'   => $data['name'],
                'email'  => strtolower($data['email']),
                'role'   => $newRole,
                'branch' => $data['branch'],
            ]);
        }

        return redirect()->route('bms.staff.index')->with('success', 'Staff updated.');
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'update');

        if (! BmsPermissions::canResetStaffPasswords(auth()->user()?->role)) {
            abort(403, 'You do not have permission to reset staff passwords.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $staff = Staff::query()->findOrFail($id);
        if (! $staff->user_id) {
            return back()->with('error', 'This staff member has no linked login account.');
        }

        User::query()->where('id', $staff->user_id)
            ->update(['password' => Hash::make($request->input('new_password'))]);

        return back()->with('success', "Password for {$staff->name} has been reset.");
    }

    public function resetPasswordByUser(Request $request, int $userId): RedirectResponse
    {
        $this->authorizeBms('staff', 'update');

        if (! BmsPermissions::canResetStaffPasswords(auth()->user()?->role)) {
            abort(403, 'You do not have permission to reset staff passwords.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::query()->findOrFail($userId);
        $user->update(['password' => Hash::make($request->input('new_password'))]);

        return back()->with('success', "Password for {$user->name} has been reset.");
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'delete');

        $staff = Staff::query()->findOrFail($id);
        $transferToId = $request->input('transfer_to_id') ? (int) $request->input('transfer_to_id') : null;

        if ($transferToId) {
            Staff::query()->findOrFail($transferToId);

            DB::table('sales_logs')->where('sales_rep_id', $id)->update(['sales_rep_id' => $transferToId]);
            DB::table('sales_logs')->where('designer_id', $id)->update(['designer_id' => $transferToId]);
            DB::table('print_jobs')->where('sales_rep_id', $id)->update(['sales_rep_id' => $transferToId]);
            DB::table('print_jobs')->where('designer_id', $id)->update(['designer_id' => $transferToId]);
            DB::table('attendance_records')->where('staff_id', $id)->update(['staff_id' => $transferToId]);
            DB::table('leave_requests')->where('staff_id', $id)->update(['staff_id' => $transferToId]);
            DB::table('payroll_entries')->where('staff_id', $id)->update(['staff_id' => $transferToId]);
        }

        $staff->delete();

        return redirect()->route('bms.staff.index')
            ->with('success', $transferToId ? 'Staff removed and records transferred.' : 'Staff removed.');
    }

    /** @return list<string> */
    private function roles(): array
    {
        $roles = array_keys(BmsSettingsDefaults::roles());
        // Admin can assign the Admin role; others only see configurable roles
        if (auth()->user()?->role === 'Admin') {
            array_unshift($roles, 'Admin');
        }
        return $roles;
    }
}


