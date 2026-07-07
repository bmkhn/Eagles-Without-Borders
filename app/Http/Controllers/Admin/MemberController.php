<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Http\Requests\Admin\MemberUpdateRequest;
use App\Models\Club;
use App\Models\Member;
use App\Models\Region;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MemberController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $q = request()->string('q')->trim()->toString();

        $membersQuery = Member::query()
            ->with(['club', 'position'])
            ->orderBy('last_name')->orderBy('first_name');

        // Club presidents are scoped to their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $membersQuery->where('club_id', $user->club_id);
        }

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('contact_number', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $members = $membersQuery->paginate(10)->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'q' => $q,
        ]);
    }

    public function directory(): View
    {
        $user = request()->user();
        $q = request()->string('q')->trim()->toString();

        // Club presidents are scoped to their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $club = \App\Models\Club::with(['region', 'members' => function ($query) use ($q) {
                $query->with('position')->orderBy('last_name')->orderBy('first_name');

                if ($q !== '') {
                    $query->where(function ($memberQuery) use ($q) {
                        $memberQuery->where('first_name', 'like', '%' . $q . '%')
                            ->orWhere('last_name', 'like', '%' . $q . '%')
                            ->orWhere('contact_number', 'like', '%' . $q . '%');
                    });
                }
            }])->findOrFail($user->club_id);

            $region = $club->region;
            $region->setRelation('clubs', collect([$club]));

            return view('admin.members.directory', [
                'regions' => collect([$region]),
                'q' => $q,
            ]);
        }

        $regions = Region::query()
            ->with(['clubs' => function ($query) use ($q) {
                $query->with(['members' => function ($memberQuery) use ($q) {
                    $memberQuery->with('position')->orderBy('last_name')->orderBy('first_name');
                }])->orderBy('name');

                if ($q !== '') {
                    $query->whereHas('members', function ($memberQuery) use ($q) {
                        $memberQuery->where('first_name', 'like', '%' . $q . '%')
                            ->orWhere('last_name', 'like', '%' . $q . '%')
                            ->orWhere('contact_number', 'like', '%' . $q . '%');
                    });
                }
            }])
            ->orderBy('name')
            ->get();

        if ($q !== '') {
            $regions = $regions->filter(function ($region) {
                $region->clubs = $region->clubs->filter(function ($club) {
                    return $club->members->count() > 0;
                });
                return $region->clubs->count() > 0;
            })->values();
        }

        return view('admin.members.directory', [
            'regions' => $regions,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        // Club presidents can only create members for their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $clubs = Club::query()->where('id', $user->club_id)->get();
        } else {
            $clubs = Club::query()->orderBy('name')->get();
        }

        return view('admin.members.create', [
            'clubs' => $clubs,
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $user = request()->user();

        $data = $request->safe()->except(['profile_picture']);

        // Club presidents can only create members for their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $data['club_id'] = $user->club_id;
        }

        $member = new Member($data);
        $member->applySlugFromName();
        $member->status = $member->status ?? 'active';

        if ($request->hasFile('profile_picture')) {
            $member->profile_picture = $this->uploadProfilePicture($request->file('profile_picture'));
        }

        $member->save();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    public function edit(Member $member): View
    {
        $user = request()->user();

        // Club presidents can only edit members for their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $clubs = Club::query()->where('id', $user->club_id)->get();
        } else {
            $clubs = Club::query()->orderBy('name')->get();
        }

        return view('admin.members.edit', [
            'member' => $member->load(['club', 'position']),
            'clubs' => $clubs,
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $user = request()->user();

        $data = $request->safe()->except(['profile_picture', 'remove_photo']);

        // Club presidents can only update members for their own club
        if ($user->hasRole('club-president') && $user->club_id) {
            $data['club_id'] = $user->club_id;
        }

        $member->fill($data);
        $member->applySlugFromName();

        if ($request->hasFile('profile_picture')) {
            // Delete old picture
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }

            $member->profile_picture = $this->uploadProfilePicture($request->file('profile_picture'));
        } elseif ($request->boolean('remove_photo') && $member->profile_picture) {
            // Remove photo checkbox checked
            Storage::disk('public')->delete($member->profile_picture);
            $member->profile_picture = null;
        }

        $member->save();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        // Delete profile picture
        if ($member->profile_picture) {
            Storage::disk('public')->delete($member->profile_picture);
        }

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    private function uploadProfilePicture($file): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->decode($file);

        // Resize to 300x300 while maintaining aspect ratio and cropping
        $image->cover(300, 300);

        $filename = uniqid('profile_') . '.webp';
        $path = 'profile-pictures/' . $filename;

        // Encode as webp at 80% quality and store
        $encoded = $image->encode(new WebpEncoder(quality: 80));
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }


}
