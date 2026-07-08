<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubStoreRequest;
use App\Http\Requests\Admin\ClubUpdateRequest;
use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $q = request()->string('q')->trim()->toString();

        $clubsQuery = Club::query()
            ->with('clubPresident')
            ->withCount('members')
            ->orderBy('name');

        // Regional admin: scope to their region
        if ($user->hasRole('regional-admin') && $user->region_id) {
            $clubsQuery->where('region_id', $user->region_id);
        }

        if ($q !== '') {
            $clubsQuery->where('name', 'like', '%' . $q . '%');
        }

        $clubs = $clubsQuery->paginate(10)->withQueryString();

        return view('admin.clubs.index', [
            'clubs' => $clubs,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        $regionsQuery = Region::query()->orderBy('name');

        // Regional admin: only their region
        if ($user->hasRole('regional-admin') && $user->region_id) {
            $regionsQuery->where('id', $user->region_id);
        }

        return view('admin.clubs.create', [
            'regions' => $regionsQuery->get(),
        ]);
    }

    public function store(ClubStoreRequest $request): RedirectResponse
    {
        $user = request()->user();
        $validated = $request->safe()->only(['region_id', 'name']);

        // Enforce region scope for regional admins
        if ($user->hasRole('regional-admin') && $user->region_id) {
            $validated['region_id'] = $user->region_id;
        }

        $club = Club::create($validated);

        // Create the club admin user
        $cpUser = User::create([
            'name' => $request->cp_name,
            'email' => $request->cp_email,
            'password' => Hash::make($request->cp_password),
            'club_id' => $club->id,
        ]);

        $cpUser->syncRoles(['club-admin']);

        activity()
            ->performedOn($club)
            ->causedBy(auth()->user())
            ->withProperties([
                'club_id' => $club->id,
                'club_name' => $club->name,
                'region_id' => $club->region_id,
                'club_admin_email' => $cpUser->email,
            ])
            ->log('created');

        return redirect()
            ->route('admin.clubs.index')
            ->with('success', 'Club created successfully with club admin account.');
    }

    public function edit(Club $club): View
    {
        $club->load('clubPresident');

        $user = request()->user();
        $regionsQuery = Region::query()->orderBy('name');

        if ($user->hasRole('regional-admin') && $user->region_id) {
            $regionsQuery->where('id', $user->region_id);
        }

        return view('admin.clubs.edit', [
            'club' => $club,
            'regions' => $regionsQuery->get(),
        ]);
    }

    public function update(ClubUpdateRequest $request, Club $club): RedirectResponse
    {
        $user = request()->user();
        $validated = $request->safe()->only(['region_id', 'name']);

        // Enforce region scope for regional admins
        if ($user->hasRole('regional-admin') && $user->region_id) {
            $validated['region_id'] = $user->region_id;
        }

        $club->update($validated);

        // Update club admin account if provided
        if ($request->filled('cp_name') || $request->filled('cp_email') || $request->filled('cp_password')) {
            $cpUser = $club->clubPresident;

            if ($cpUser) {
                $data = [];
                if ($request->filled('cp_name')) {
                    $data['name'] = $request->cp_name;
                }
                if ($request->filled('cp_email')) {
                    $data['email'] = $request->cp_email;
                }
                if ($request->filled('cp_password')) {
                    $data['password'] = Hash::make($request->cp_password);
                }
                $cpUser->update($data);
            } else {
                $cpUser = User::create([
                    'name' => $request->cp_name,
                    'email' => $request->cp_email,
                    'password' => Hash::make($request->cp_password),
                    'club_id' => $club->id,
                ]);
                $cpUser->syncRoles(['club-admin']);
            }
        }

        activity()
            ->performedOn($club)
            ->causedBy(auth()->user())
            ->withProperties([
                'club_id' => $club->id,
                'club_name' => $club->name,
                'region_id' => $club->region_id,
            ])
            ->log('updated');

        return redirect()
            ->route('admin.clubs.index')
            ->with('success', 'Club updated successfully.');
    }

    public function destroy(Club $club): RedirectResponse
    {
        if ($club->members()->exists()) {
            return redirect()
                ->route('admin.clubs.index')
                ->with('error', 'Cannot delete club because it still contains members');
        }

        // Remove the club admin account
        $club->load('clubPresident');
        if ($club->clubPresident) {
            $club->clubPresident->delete();
        }

        activity()
            ->performedOn($club)
            ->causedBy(auth()->user())
            ->withProperties([
                'club_id' => $club->id,
                'club_name' => $club->name,
            ])
            ->log('deleted');

        $club->delete();

        return redirect()
            ->route('admin.clubs.index')
            ->with('success', 'Club deleted successfully.');
    }
}
