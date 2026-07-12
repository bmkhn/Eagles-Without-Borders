<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isNationalAdmin = $user->hasRole('national-admin');
        $isSuperAdmin = $user->hasRole('super-admin');

        $q = request()->string('q')->trim()->toString();
        $filterRole = request()->string('role')->trim()->toString();

        $adminsQuery = User::query()
            ->whereHas('roles')
            ->with('roles', 'club', 'region')
            ->orderBy('name');

        // National Admin cannot see Super Admin accounts
        if ($isNationalAdmin) {
            $adminsQuery->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super-admin');
            });
        }

        if ($q !== '') {
            $adminsQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        if ($filterRole !== '' && in_array($filterRole, ['super-admin', 'national-admin', 'regional-admin', 'club-admin'])) {
            $adminsQuery->whereHas('roles', function ($q) use ($filterRole) {
                $q->where('name', $filterRole);
            });
        }

        $admins = $adminsQuery->paginate(15)->withQueryString();

        return view('admin.admins.index', [
            'admins' => $admins,
            'q' => $q,
            'filterRole' => $filterRole,
            'isNationalAdmin' => $isNationalAdmin,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        return view('admin.admins.create', [
            'regions' => Region::query()->orderBy('name')->get(),
            'clubs' => Club::query()->orderBy('name')->get(),
            'isNationalAdmin' => $user->hasRole('national-admin'),
            'isSuperAdmin' => $user->hasRole('super-admin'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = request()->user();
        $isNationalAdmin = $user->hasRole('national-admin');

        // National Admin cannot create Super Admin accounts
        $allowedRoles = $isNationalAdmin
            ? 'national-admin,regional-admin,club-admin'
            : 'super-admin,national-admin,regional-admin,club-admin';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . $allowedRoles],
            'region_id' => ['nullable', 'required_if:role,regional-admin', 'integer', 'exists:regions,id'],
            'club_id' => ['nullable', 'required_if:role,club-admin', 'integer', 'exists:clubs,id'],
        ]);

        $adminUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'region_id' => $validated['region_id'] ?? null,
            'club_id' => $validated['club_id'] ?? null,
        ]);

        $adminUser->syncRoles([$validated['role']]);

        activity()
            ->performedOn($adminUser)
            ->causedBy(auth()->user())
            ->withProperties(['role' => $validated['role']])
            ->log('created_admin');

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin account created successfully.');
    }

    public function edit(User $admin): View
    {
        if (!$admin->roles->isNotEmpty()) {
            abort(404, 'Not an admin account.');
        }

        $user = request()->user();
        $isNationalAdmin = $user->hasRole('national-admin');

        // National Admin cannot edit Super Admin accounts
        $adminRole = $admin->roles->first()?->name;
        if ($isNationalAdmin && $adminRole === 'super-admin') {
            abort(403, 'You cannot edit Super Admin accounts.');
        }

        $admin->load('roles', 'club', 'region');

        return view('admin.admins.edit', [
            'admin' => $admin,
            'regions' => Region::query()->orderBy('name')->get(),
            'clubs' => Club::query()->orderBy('name')->get(),
            'currentRole' => $adminRole ?? 'club-admin',
            'isNationalAdmin' => $isNationalAdmin,
        ]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        if (!$admin->roles->isNotEmpty()) {
            abort(404, 'Not an admin account.');
        }

        $user = request()->user();
        $isNationalAdmin = $user->hasRole('national-admin');

        // National Admin cannot update Super Admin accounts
        $adminRole = $admin->roles->first()?->name;
        if ($isNationalAdmin && $adminRole === 'super-admin') {
            abort(403, 'You cannot update Super Admin accounts.');
        }

        // National Admin cannot set role to super-admin
        $allowedRoles = $isNationalAdmin
            ? 'national-admin,regional-admin,club-admin'
            : 'super-admin,national-admin,regional-admin,club-admin';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($admin->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . $allowedRoles],
            'region_id' => ['nullable', 'required_if:role,regional-admin', 'integer', 'exists:regions,id'],
            'club_id' => ['nullable', 'required_if:role,club-admin', 'integer', 'exists:clubs,id'],
        ]);

        // Capture original values for audit diff
        $original = [
            'name' => $admin->getOriginal('name'),
            'email' => $admin->getOriginal('email'),
            'region_id' => $admin->getOriginal('region_id'),
            'club_id' => $admin->getOriginal('club_id'),
            'role' => $adminRole,
        ];

        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'region_id' => $validated['region_id'] ?? null,
            'club_id' => $validated['club_id'] ?? null,
        ]);

        if ($request->filled('password')) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();
        $admin->syncRoles([$validated['role']]);

        $newValues = [
            'name' => $admin->name,
            'email' => $admin->email,
            'region_id' => $admin->region_id,
            'club_id' => $admin->club_id,
            'role' => $validated['role'],
        ];

        $changes = [];
        foreach ($newValues as $key => $newVal) {
            $oldVal = $original[$key] ?? null;
            if ((string) $oldVal !== (string) $newVal) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        activity()
            ->performedOn($admin)
            ->causedBy(auth()->user())
            ->withProperties([
                'changes' => $changes,
                'password_updated' => $request->filled('password'),
            ])
            ->log('updated_admin');

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin account updated successfully.');
    }

    public function destroy(Request $request, User $admin): RedirectResponse
    {
        if (!$admin->roles->isNotEmpty()) {
            abort(404, 'Not an admin account.');
        }

        $user = request()->user();
        $isNationalAdmin = $user->hasRole('national-admin');

        // National Admin cannot delete Super Admin accounts
        $adminRole = $admin->roles->first()?->name;
        if ($isNationalAdmin && $adminRole === 'super-admin') {
            abort(403, 'You cannot delete Super Admin accounts.');
        }

        // Prevent self-deletion
        if ($admin->id === auth()->id()) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Require confirmation
        $request->validate([
            'confirm_delete' => ['required', 'accepted'],
            'confirm_text' => ['required', 'string', 'in:DELETE'],
        ]);

        activity()
            ->performedOn($admin)
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
            ])
            ->log('deleted_admin');

        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin account deleted successfully.');
    }

    public function auditLogs(): View
    {
        $q = request()->string('q')->trim()->toString();
        $filterEvent = request()->string('event')->trim()->toString();
        $filterLogName = request()->string('log_name')->trim()->toString();

        $logsQuery = Activity::query()->with('causer')->latest();

        if ($q !== '') {
            $logsQuery->where(function ($query) use ($q) {
                $query->where('description', 'like', '%' . $q . '%')
                    ->orWhere('properties', 'like', '%' . $q . '%')
                    ->orWhereHas('causer', function ($cq) use ($q) {
                        $cq->where('name', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        if ($filterEvent !== '') {
            $logsQuery->where('description', $filterEvent);
        }

        if ($filterLogName !== '') {
            $logsQuery->where('log_name', $filterLogName);
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        [$eventTypes, $logNames] = Cache::remember('audit_log_filter_options', 3600, function () {
            $eventTypes = Activity::query()
                ->select('description')
                ->distinct()
                ->pluck('description')
                ->toArray();

            $logNames = Activity::query()
                ->select('log_name')
                ->distinct()
                ->pluck('log_name')
                ->toArray();

            return [$eventTypes, $logNames];
        });

        return view('admin.audit-logs', [
            'logs' => $logs,
            'q' => $q,
            'filterEvent' => $filterEvent,
            'filterLogName' => $filterLogName,
            'eventTypes' => $eventTypes,
            'logNames' => $logNames,
        ]);
    }
}
