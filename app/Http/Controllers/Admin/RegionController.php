<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegionStoreRequest;
use App\Http\Requests\Admin\RegionUpdateRequest;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $q = request()->string('q')->trim()->toString();

        $regionsQuery = Region::query()
            ->with('regionalAdmin')
            ->withCount('clubs')
            ->orderBy('name');

        if ($q !== '') {
            $regionsQuery->where('name', 'like', '%' . $q . '%');
        }

        $regions = $regionsQuery->paginate(10)->withQueryString();

        return view('admin.regions.index', [
            'regions' => $regions,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.regions.create');
    }

    public function store(RegionStoreRequest $request): RedirectResponse
    {
        $region = Region::create($request->safe()->only(['name']));

        // Create the regional admin user
        $user = User::create([
            'name' => $request->ra_name,
            'email' => $request->ra_email,
            'password' => Hash::make($request->ra_password),
            'region_id' => $region->id,
        ]);

        $user->syncRoles(['regional-admin']);

        activity()
            ->performedOn($region)
            ->causedBy(auth()->user())
            ->withProperties([
                'region_id' => $region->id,
                'region_name' => $region->name,
                'regional_admin_email' => $user->email,
            ])
            ->log('created');

        return redirect()
            ->route('admin.regions.index')
            ->with('success', 'Region created successfully with regional admin account.');
    }

    public function edit(Region $region): View
    {
        $region->load('regionalAdmin');

        return view('admin.regions.edit', [
            'region' => $region,
        ]);
    }

    public function update(RegionUpdateRequest $request, Region $region): RedirectResponse
    {
        $region->update($request->safe()->only(['name']));

        // Update regional admin account if provided
        if ($request->filled('ra_name') || $request->filled('ra_email') || $request->filled('ra_password')) {
            $raUser = $region->regionalAdmin;

            if ($raUser) {
                $data = [];
                if ($request->filled('ra_name')) {
                    $data['name'] = $request->ra_name;
                }
                if ($request->filled('ra_email')) {
                    $data['email'] = $request->ra_email;
                }
                if ($request->filled('ra_password')) {
                    $data['password'] = Hash::make($request->ra_password);
                }
                $raUser->update($data);
            } else {
                $raUser = User::create([
                    'name' => $request->ra_name,
                    'email' => $request->ra_email,
                    'password' => Hash::make($request->ra_password),
                    'region_id' => $region->id,
                ]);
                $raUser->syncRoles(['regional-admin']);
            }
        }

        activity()
            ->performedOn($region)
            ->causedBy(auth()->user())
            ->withProperties([
                'region_id' => $region->id,
                'region_name' => $region->name,
            ])
            ->log('updated');

        return redirect()
            ->route('admin.regions.index')
            ->with('success', 'Region updated successfully.');
    }

    public function destroy(Region $region): RedirectResponse
    {
        if ($region->clubs()->exists()) {
            return redirect()
                ->route('admin.regions.index')
                ->with('error', 'Cannot delete region because it still contains clubs');
        }

        // Remove the regional admin account
        $region->load('regionalAdmin');
        if ($region->regionalAdmin) {
            $region->regionalAdmin->delete();
        }

        activity()
            ->performedOn($region)
            ->causedBy(auth()->user())
            ->withProperties([
                'region_id' => $region->id,
                'region_name' => $region->name,
            ])
            ->log('deleted');

        $region->delete();

        return redirect()
            ->route('admin.regions.index')
            ->with('success', 'Region deleted successfully.');
    }
}
