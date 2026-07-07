<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Http\Requests\Admin\MemberUpdateRequest;
use App\Models\Certificate;
use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
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
        $filterRegionId = request()->integer('region_id');
        $filterClubId = request()->integer('club_id');
        $filterStatus = request()->string('status')->trim()->toString();
        $filterPositionId = request()->integer('position_id');

        $isClubPresident = $user->hasRole('club-president') && $user->club_id;
        $isNationalPresident = $user->hasRole('national-president');

        $membersQuery = Member::query()
            ->with(['club.region', 'position']);

        if ($isClubPresident) {
            $membersQuery->where('club_id', $user->club_id);
        }

        if ($filterRegionId && $isNationalPresident) {
            $membersQuery->whereHas('club', function ($q) use ($filterRegionId) {
                $q->where('region_id', $filterRegionId);
            });
        }

        if ($filterClubId) {
            $membersQuery->where('club_id', $filterClubId);
        }

        if ($filterStatus !== '' && in_array($filterStatus, ['active', 'inactive'])) {
            $membersQuery->where('status', $filterStatus);
        }

        if ($filterPositionId) {
            $membersQuery->where('position_id', $filterPositionId);
        }

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('contact_number', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $members = $membersQuery->orderBy('last_name')->orderBy('first_name')
            ->paginate(10)->withQueryString();

        $regions = $isNationalPresident ? Region::query()->orderBy('name')->get() : collect();
        $clubs = Club::query()->orderBy('name');
        if ($filterRegionId && $isNationalPresident) {
            $clubs->where('region_id', $filterRegionId);
        }
        if ($isClubPresident) {
            $clubs->where('id', $user->club_id);
        }
        $clubs = $clubs->get();

        $positions = Position::query()->orderBy('name')->get();

        return view('admin.members.index', [
            'members' => $members,
            'q' => $q,
            'filterRegionId' => $filterRegionId,
            'filterClubId' => $filterClubId,
            'filterStatus' => $filterStatus,
            'filterPositionId' => $filterPositionId,
            'regions' => $regions,
            'clubs' => $clubs,
            'positions' => $positions,
            'isClubPresident' => $isClubPresident,
            'isNationalPresident' => $isNationalPresident,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

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

        $data = $request->safe()->except(['profile_picture', 'certificates']);

        if ($user->hasRole('club-president') && $user->club_id) {
            $data['club_id'] = $user->club_id;
        }

        $member = new Member($data);
        $member->applySlugFromName();
        $member->status = $member->status ?? 'active';

        if ($request->hasFile('profile_picture')) {
            $member->profile_picture = $this->storeProfilePicture($request->file('profile_picture'));
        }

        $member->save();

        if ($request->has('certificates')) {
            $this->syncCertificates($member, $request);
        }

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    public function edit(Member $member): View
    {
        $user = request()->user();

        if ($user->hasRole('club-president') && $user->club_id) {
            $clubs = Club::query()->where('id', $user->club_id)->get();
        } else {
            $clubs = Club::query()->orderBy('name')->get();
        }

        return view('admin.members.edit', [
            'member' => $member->load(['club', 'position', 'certificates']),
            'clubs' => $clubs,
            'positions' => Position::query()->orderBy('name')->get(),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $user = request()->user();

        $data = $request->safe()->except(['profile_picture', 'remove_photo', 'certificates']);

        if ($user->hasRole('club-president') && $user->club_id) {
            $data['club_id'] = $user->club_id;
        }

        $member->fill($data);
        $member->applySlugFromName();

        if ($request->hasFile('profile_picture')) {
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }
            $member->profile_picture = $this->storeProfilePicture($request->file('profile_picture'));
        } elseif ($request->boolean('remove_photo') && $member->profile_picture) {
            Storage::disk('public')->delete($member->profile_picture);
            $member->profile_picture = null;
        }

        $member->save();

        if ($request->has('certificates') || $request->boolean('certificates_managed')) {
            $this->syncCertificates($member, $request);
        }

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->profile_picture) {
            Storage::disk('public')->delete($member->profile_picture);
        }

        foreach ($member->certificates as $cert) {
            if ($cert->file) {
                Storage::disk('public')->delete($cert->file);
            }
        }

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Store a profile picture with aggressive optimization: 300×300, WebP at 60% quality.
     */
    private function storeProfilePicture(UploadedFile $file): string
    {
        return $this->optimizeAndStoreImage($file, 'profile-pictures', 300, 300, 60);
    }

    /**
     * Store and optimize an uploaded file.
     *
     * - Images: resized to fit within max dimensions, converted to WebP at given quality
     * - PDFs: stored as-is (no server-side compression available)
     */
    private function optimizeAndStoreImage(UploadedFile $file, string $directory, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 70): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->decode($file);

        // Resize to fit within max dimensions while maintaining aspect ratio
        $image->scale(width: $maxWidth, height: $maxHeight);

        $filename = uniqid('img_') . '.webp';
        $path = $directory . '/' . $filename;

        $encoded = $image->encode(new WebpEncoder(quality: $quality));
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    /**
     * Store a certificate file with optimization.
     *
     * Images are resized/converted to WebP. PDFs are stored as-is.
     */
    private function storeCertificateFile(UploadedFile $file): string
    {
        $mime = $file->getMimeType();

        // Images: optimize by resizing and converting to WebP
        if (str_starts_with($mime, 'image/')) {
            return $this->optimizeAndStoreImage($file, 'certificates', 1200, 1200, 70);
        }

        // PDFs and other files: store as-is
        $extension = $file->getClientOriginalExtension() ?: 'pdf';
        $filename = uniqid('cert_') . '.' . $extension;

        return $file->storeAs('certificates', $filename, 'public');
    }

    private function syncCertificates(Member $member, MemberStoreRequest|MemberUpdateRequest $request): void
    {
        $certificates = $request->input('certificates', []);
        $existingIds = [];
        $memberCertIds = $member->certificates()->pluck('id')->all();

        foreach ($certificates as $index => $certData) {
            $certId = $certData['id'] ?? null;

            if (empty($certData['name']) && !$request->hasFile("certificates.{$index}.file")) {
                continue;
            }

            $data = [
                'name' => $certData['name'] ?? '',
                'issued_at' => $certData['issued_at'] ?? null,
            ];

            if ($certId && in_array($certId, $memberCertIds)) {
                $cert = Certificate::find($certId);
                if ($cert) {
                    if ($request->hasFile("certificates.{$index}.file")) {
                        if ($cert->file) {
                            Storage::disk('public')->delete($cert->file);
                        }
                        $data['file'] = $this->storeCertificateFile($request->file("certificates.{$index}.file"));
                    }
                    $cert->update($data);
                    $existingIds[] = $cert->id;
                }
            } else {
                $data['member_id'] = $member->id;
                if ($request->hasFile("certificates.{$index}.file")) {
                    $data['file'] = $this->storeCertificateFile($request->file("certificates.{$index}.file"));
                }
                $cert = Certificate::create($data);
                $existingIds[] = $cert->id;
            }
        }

        $member->certificates()
            ->whereNotIn('id', $existingIds)
            ->each(function ($cert) {
                if ($cert->file) {
                    Storage::disk('public')->delete($cert->file);
                }
                $cert->delete();
            });
    }
}
