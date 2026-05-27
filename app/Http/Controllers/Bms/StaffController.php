<?php

namespace App\Http\Controllers\Bms;

use App\Models\Staff;
use App\Models\User;
use App\Support\BmsSettingsDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('staff', 'read');

        $branch = request('branch', $this->branchFilter() ?? 'all');
        $q = trim((string) request('q', ''));

        $query = Staff::query()->orderBy('name');

        if ($branch !== 'all') {
            $query->where('branch', $branch);
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

        $password = $data['password'] ?? ('QP@'.bin2hex(random_bytes(4)).'1!');
        unset($data['password']); // staff table has no password column

        $staff = Staff::query()->create($data);
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

        $showPassword = $request->input('password') ? null : $password;

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
        $data = $request->validate([
            'name'   => 'required|string|max:120',
            'email'  => 'required|email',
            'role'   => 'required|string|max:80',
            'branch' => 'required|string|max:80',
            'salary' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
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

        // Only Admin can reset passwords
        if (auth()->user()?->role !== 'Admin') {
            abort(403, 'Only Admin users can reset staff passwords.');
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

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('staff', 'delete');
        Staff::query()->findOrFail($id)->delete();

        return redirect()->route('bms.staff.index')->with('success', 'Staff removed.');
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


