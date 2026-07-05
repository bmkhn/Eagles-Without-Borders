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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MemberController extends Controller
{
    public function index(): View
    {
        $q = request()->string('q')->trim()->toString();

        $membersQuery = Member::query()
            ->with(['club', 'position'])
            ->orderBy('name');

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
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
        $q = request()->string('q')->trim()->toString();

        $regions = Region::query()
            ->with(['clubs' => function ($query) use ($q) {
                $query->with(['members' => function ($memberQuery) use ($q) {
                    $memberQuery->with('position')->orderBy('name');
                }])->orderBy('name');

                if ($q !== '') {
                    $query->whereHas('members', function ($memberQuery) use ($q) {
                        $memberQuery->where('name', 'like', '%' . $q . '%')
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
        return view('admin.members.create', [
            'clubs' => Club::query()->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $member = new Member($request->safe()->except(['profile_picture']));
        $member->applySlugFromName();

        if ($request->hasFile('profile_picture')) {
            $member->profile_picture = $this->uploadProfilePicture($request->file('profile_picture'));
        }

        $member->save();

        // Generate QR code after save so the member has an ID
        $this->generateQrCode($member);

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    public function edit(Member $member): View
    {
        return view('admin.members.edit', [
            'member' => $member->load(['club', 'position']),
            'clubs' => Club::query()->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $member->fill($request->safe()->except(['profile_picture', 'remove_photo']));
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

        // Regenerate QR code (URL may have changed if slug changed)
        $this->generateQrCode($member);

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

        // Delete QR code
        if ($member->qr_code) {
            Storage::disk('public')->delete($member->qr_code);
        }

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    private function uploadProfilePicture($file): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        // Resize to 300x300 while maintaining aspect ratio and cropping
        $image->cover(300, 300);

        $filename = uniqid('profile_') . '.webp';
        $path = 'profile-pictures/' . $filename;

        // Encode as webp and store
        $encoded = $image->toWebp(80);
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    private function generateQrCode(Member $member): void
    {
        $profileUrl = route('member.profile', $member->slug);

        $qrSvg = app('qrcode')
            ->size(300)
            ->margin(5)
            ->color(245, 158, 11)
            ->backgroundColor(0, 0, 0, 0)
            ->generate($profileUrl);

        $filename = 'qr_' . $member->id . '_' . uniqid() . '.svg';
        $path = 'qr-codes/' . $filename;

        Storage::disk('public')->put($path, (string) $qrSvg);

        // Delete old QR code if exists
        if ($member->qr_code) {
            Storage::disk('public')->delete($member->qr_code);
        }

        $member->updateQuietly(['qr_code' => $path]);
    }
}
