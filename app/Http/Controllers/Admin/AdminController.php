<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Member;
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
        $user = request()->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $q = request()->string('q')->trim()->toString();
        $filterEvent = request()->string('event')->trim()->toString();
        $filterLogName = request()->string('log_name')->trim()->toString();

        $logsQuery = Activity::query()->with('causer')->latest();

        // Super Admin & National Admin see everything
        if ($isSuperAdmin || $isNationalAdmin) {
            // No scoping needed
        }
        // Regional Admin: scope to their region
        elseif ($isRegionalAdmin) {
            $regionId = (int) $user->region_id;
            $clubIdsInRegion = Club::where('region_id', $regionId)->pluck('id')->toArray();
            $memberIdsInRegion = Member::withTrashed()
                ->whereHas('club', fn ($q) => $q->where('region_id', $regionId))
                ->pluck('id')
                ->toArray();

            $logsQuery->where(function ($query) use ($user, $regionId, $clubIdsInRegion, $memberIdsInRegion) {
                // 1. Activities they caused themselves
                $query->orWhere('causer_id', $user->id);

                // 2. Subject is a member in their region
                if (!empty($memberIdsInRegion)) {
                    $query->orWhere(function ($q) use ($memberIdsInRegion) {
                        $q->whereIn('subject_id', $memberIdsInRegion)
                          ->where('subject_type', (new Member)->getMorphClass());
                    });
                }

                // 3. Subject is a club in their region
                if (!empty($clubIdsInRegion)) {
                    $query->orWhere(function ($q) use ($clubIdsInRegion) {
                        $q->whereIn('subject_id', $clubIdsInRegion)
                          ->where('subject_type', (new Club)->getMorphClass());
                    });
                }

                // 4. Subject is their region
                $query->orWhere(function ($q) use ($regionId) {
                    $q->where('subject_id', $regionId)
                      ->where('subject_type', (new Region)->getMorphClass());
                });

                // 5. Properties contain their region_id (catches activities without a subject model)
                $query->orWhereJsonContains('properties->region_id', $regionId);
                // Also catch region moves: changes->region_id->old/new (club moved out/in of region)
                $query->orWhereJsonContains('properties->changes->region_id->old', (string) $regionId);
                $query->orWhereJsonContains('properties->changes->region_id->new', (string) $regionId);

                // 6. Properties contain club_ids of clubs in their region (catches member moves in/out)
                foreach ($clubIdsInRegion as $cid) {
                    // Exact top-level integer match
                    $query->orWhereJsonContains('properties->club_id', $cid);
                    // Nested changes match for moves (values stored as strings in changes)
                    $query->orWhereJsonContains('properties->changes->club_id->old', (string) $cid);
                    $query->orWhereJsonContains('properties->changes->club_id->new', (string) $cid);
                }
            });
        }
        // Club Admin: scope to their club
        elseif ($isClubAdmin) {
            $clubId = (int) $user->club_id;
            $memberIdsInClub = Member::withTrashed()
                ->where('club_id', $clubId)
                ->pluck('id')
                ->toArray();

            $logsQuery->where(function ($query) use ($user, $clubId, $memberIdsInClub) {
                // 1. Activities they caused themselves
                $query->orWhere('causer_id', $user->id);

                // 2. Subject is a member in their club
                if (!empty($memberIdsInClub)) {
                    $query->orWhere(function ($q) use ($memberIdsInClub) {
                        $q->whereIn('subject_id', $memberIdsInClub)
                          ->where('subject_type', (new Member)->getMorphClass());
                    });
                }

                // 3. Subject is their club
                $query->orWhere(function ($q) use ($clubId) {
                    $q->where('subject_id', $clubId)
                      ->where('subject_type', (new Club)->getMorphClass());
                });

                // 4. Properties contain their club_id (catches member moves in/out)
                $query->orWhereJsonContains('properties->club_id', $clubId);
                // Nested changes match for moves (values stored as strings in changes)
                $query->orWhereJsonContains('properties->changes->club_id->old', (string) $clubId);
                $query->orWhereJsonContains('properties->changes->club_id->new', (string) $clubId);
            });
        }

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

        [$eventTypes, $logNames] = Cache::remember('audit_log_filter_options', 3600, function () use ($user, $isSuperAdmin, $isNationalAdmin, $isRegionalAdmin, $isClubAdmin) {
            $eventTypesQuery = Activity::query()->select('description')->distinct();
            $logNamesQuery = Activity::query()->select('log_name')->distinct();

            // Scope the cached filter options too
            if ($isRegionalAdmin && $user->region_id) {
                $regionId = (int) $user->region_id;
                $clubIdsInRegion = Club::where('region_id', $regionId)->pluck('id')->toArray();
                $memberIdsInRegion = Member::withTrashed()
                    ->whereHas('club', fn ($q) => $q->where('region_id', $regionId))
                    ->pluck('id')
                    ->toArray();

                $scopeFilter = function ($q) use ($user, $regionId, $clubIdsInRegion, $memberIdsInRegion) {
                    $q->where('causer_id', $user->id)
                      ->orWhere(function ($sq) use ($memberIdsInRegion) {
                          if (!empty($memberIdsInRegion)) {
                              $sq->whereIn('subject_id', $memberIdsInRegion)
                                 ->where('subject_type', (new Member)->getMorphClass());
                          }
                      })->orWhere(function ($sq) use ($clubIdsInRegion) {
                          if (!empty($clubIdsInRegion)) {
                              $sq->whereIn('subject_id', $clubIdsInRegion)
                                 ->where('subject_type', (new Club)->getMorphClass());
                          }
                      })->orWhere(function ($sq) use ($regionId) {
                          $sq->where('subject_id', $regionId)
                             ->where('subject_type', (new Region)->getMorphClass());
                      })->orWhereJsonContains('properties->region_id', $regionId)
                          ->orWhereJsonContains('properties->changes->region_id->old', (string) $regionId)
                          ->orWhereJsonContains('properties->changes->region_id->new', (string) $regionId);

                    foreach ($clubIdsInRegion as $cid) {
                        $q->orWhereJsonContains('properties->club_id', $cid)
                          ->orWhereJsonContains('properties->changes->club_id->old', (string) $cid)
                          ->orWhereJsonContains('properties->changes->club_id->new', (string) $cid);
                    }
                };

                $eventTypesQuery->where($scopeFilter);
                $logNamesQuery->where($scopeFilter);
            } elseif ($isClubAdmin && $user->club_id) {
                $clubId = (int) $user->club_id;
                $memberIdsInClub = Member::withTrashed()
                    ->where('club_id', $clubId)
                    ->pluck('id')
                    ->toArray();

                $scopeFilter = function ($q) use ($user, $clubId, $memberIdsInClub) {
                    $q->where('causer_id', $user->id)
                      ->orWhere(function ($sq) use ($memberIdsInClub) {
                          if (!empty($memberIdsInClub)) {
                              $sq->whereIn('subject_id', $memberIdsInClub)
                                 ->where('subject_type', (new Member)->getMorphClass());
                          }
                      })->orWhere(function ($sq) use ($clubId) {
                          $sq->where('subject_id', $clubId)
                             ->where('subject_type', (new Club)->getMorphClass());
                      })->orWhereJsonContains('properties->club_id', $clubId)
                          ->orWhereJsonContains('properties->changes->club_id->old', (string) $clubId)
                          ->orWhereJsonContains('properties->changes->club_id->new', (string) $clubId);
                };

                $eventTypesQuery->where($scopeFilter);
                $logNamesQuery->where($scopeFilter);
            }

            $eventTypes = $eventTypesQuery->pluck('description')->toArray();
            $logNames = $logNamesQuery->pluck('log_name')->toArray();

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
