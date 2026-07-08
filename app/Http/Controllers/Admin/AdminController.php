<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function index(): View
    {
        $q = request()->string('q')->trim()->toString();
        $filterRole = request()->string('role')->trim()->toString();

        $adminsQuery = User::query()
            ->whereHas('roles')
            ->with('roles', 'club', 'region')
            ->orderBy('name');

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
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'regions' => Region::query()->orderBy('name')->get(),
            'clubs' => Club::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:super-admin,national-admin,regional-admin,club-admin'],
            'region_id' => ['nullable', 'required_if:role,regional-admin', 'integer', 'exists:regions,id'],
            'club_id' => ['nullable', 'required_if:role,club-admin', 'integer', 'exists:clubs,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'region_id' => $validated['region_id'] ?? null,
            'club_id' => $validated['club_id'] ?? null,
        ]);

        $user->syncRoles([$validated['role']]);

        activity()
            ->performedOn($user)
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

        $admin->load('roles', 'club', 'region');

        return view('admin.admins.edit', [
            'admin' => $admin,
            'regions' => Region::query()->orderBy('name')->get(),
            'clubs' => Club::query()->orderBy('name')->get(),
            'currentRole' => $admin->roles->first()?->name ?? 'club-admin',
        ]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        if (!$admin->roles->isNotEmpty()) {
            abort(404, 'Not an admin account.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($admin->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:super-admin,national-admin,regional-admin,club-admin'],
            'region_id' => ['nullable', 'required_if:role,regional-admin', 'integer', 'exists:regions,id'],
            'club_id' => ['nullable', 'required_if:role,club-admin', 'integer', 'exists:clubs,id'],
        ]);

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

        activity()
            ->performedOn($admin)
            ->causedBy(auth()->user())
            ->withProperties(['role' => $validated['role']])
            ->log('updated_admin');

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin account updated successfully.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        if (!$admin->roles->isNotEmpty()) {
            abort(404, 'Not an admin account.');
        }

        // Prevent self-deletion
        if ($admin->id === auth()->id()) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

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
