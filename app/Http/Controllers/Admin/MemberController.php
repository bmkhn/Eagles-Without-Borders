<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberImportRequest;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Http\Requests\Admin\MemberUpdateRequest;
use App\Models\Certificate;
use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $membersQuery = Member::query()
            ->with(['club.region', 'position']);

        if ($isClubAdmin) {
            $membersQuery->where('club_id', $user->club_id);
        }

        if ($isRegionalAdmin) {
            $membersQuery->whereHas('club', function ($q) use ($user) {
                $q->where('region_id', $user->region_id);
            });
        }

        if ($filterRegionId && ($isSuperAdmin || $isNationalAdmin)) {
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

        $totalCount = (clone $membersQuery)->count();

        // Unfiltered total (role-scoped but without ad-hoc filters)
        $unfilteredQuery = Member::query();
        if ($isClubAdmin) {
            $unfilteredQuery->where('club_id', $user->club_id);
        }
        if ($isRegionalAdmin) {
            $unfilteredQuery->whereHas('club', function ($q) use ($user) {
                $q->where('region_id', $user->region_id);
            });
        }
        $unfilteredTotal = (clone $unfilteredQuery)->count();

        $members = $membersQuery->orderBy('last_name')->orderBy('first_name')
            ->paginate(10)->withQueryString();

        $regions = ($isSuperAdmin || $isNationalAdmin) ? Region::query()->orderBy('name')->get() : collect();
        $clubsQuery = Club::query()->orderBy('name');

        if ($isRegionalAdmin) {
            $clubsQuery->where('region_id', $user->region_id);
        }

        if ($filterRegionId && ($isSuperAdmin || $isNationalAdmin)) {
            $clubsQuery->where('region_id', $filterRegionId);
        }
        if ($isClubAdmin) {
            $clubsQuery->where('id', $user->club_id);
        }
        $clubs = $clubsQuery->get();

        $positionsQuery = Position::query()->orderBy('name');

        if ($isClubAdmin || $isRegionalAdmin) {
            $positionsQuery->where('name', '!=', 'National President');
        }

        $positions = $positionsQuery->get();

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
            'totalCount' => $totalCount,
            'unfilteredTotal' => $unfilteredTotal,
            'isClubAdmin' => $isClubAdmin,
            'isSuperAdmin' => $isSuperAdmin,
            'isNationalAdmin' => $isNationalAdmin,
            'isRegionalAdmin' => $isRegionalAdmin,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        if ($isClubAdmin) {
            $clubs = Club::query()->where('id', $user->club_id)->get();
        } elseif ($isRegionalAdmin) {
            $clubs = Club::query()->where('region_id', $user->region_id)->get();
        } else {
            $clubs = Club::query()->orderBy('name')->get();
        }

        $positionsQuery = Position::query()->orderBy('name');
        if ($isClubAdmin || $isRegionalAdmin) {
            $positionsQuery->where('name', '!=', 'National President');
        }

        return view('admin.members.create', [
            'clubs' => $clubs,
            'positions' => $positionsQuery->get(),
        ]);
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $data = $request->safe()->except(['profile_picture', 'certificates']);

        if ($isClubAdmin) {
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

        $member->load('club.region');

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'club' => $member->club?->name,
                'region' => $member->club?->region?->name,
                'position' => $member->position?->name,
                'status' => $member->status,
                'contact_number' => $member->contact_number,
                'source' => 'manual_create',
            ])
            ->log('created');

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    public function edit(Member $member): View
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        if ($isClubAdmin) {
            $clubs = Club::query()->where('id', $user->club_id)->get();
        } elseif ($isRegionalAdmin) {
            $clubs = Club::query()->where('region_id', $user->region_id)->get();
        } else {
            $clubs = Club::query()->orderBy('name')->get();
        }

        $positionsQuery = Position::query()->orderBy('name');
        if ($isClubAdmin || $isRegionalAdmin) {
            $positionsQuery->where('name', '!=', 'National President');
        }

        return view('admin.members.edit', [
            'member' => $member->load(['club', 'position', 'certificates', 'payments']),
            'clubs' => $clubs,
            'positions' => $positionsQuery->get(),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $data = $request->safe()->except(['profile_picture', 'remove_photo', 'certificates']);

        if ($isClubAdmin) {
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

        $member->load('club.region');

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'club' => $member->club?->name,
                'region' => $member->club?->region?->name,
                'position' => $member->position?->name,
                'status' => $member->status,
                'contact_number' => $member->contact_number,
            ])
            ->log('updated');

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Export members to CSV with all current filters applied.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = request()->user();

        $q = request()->string('q')->trim()->toString();
        $filterRegionId = request()->integer('region_id');
        $filterClubId = request()->integer('club_id');
        $filterStatus = request()->string('status')->trim()->toString();
        $filterPositionId = request()->integer('position_id');

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        $membersQuery = Member::query()
            ->with(['club.region', 'position'])
            ->orderBy('last_name')->orderBy('first_name');

        // Role scoping
        if ($isClubAdmin) {
            $membersQuery->where('club_id', $user->club_id);
        } elseif ($isRegionalAdmin) {
            $membersQuery->whereHas('club', fn ($q) => $q->where('region_id', $user->region_id));
        }

        // Apply same filters as index
        if ($filterRegionId && ($isSuperAdmin || $isNationalAdmin)) {
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

        $members = $membersQuery->get();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'count' => $members->count(),
                'filters' => array_filter([
                    'q' => $q ?: null,
                    'region_id' => $filterRegionId ?: null,
                    'club_id' => $filterClubId ?: null,
                    'status' => $filterStatus ?: null,
                    'position_id' => $filterPositionId ?: null,
                ]),
            ])
            ->log('exported_members');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="members-export-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($members) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['First Name', 'M.I.', 'Last Name', 'Suffix', 'Contact Number', 'Club', 'Region', 'Position', 'Status']);

            foreach ($members as $member) {
                fputcsv($handle, [
                    $member->first_name,
                    $member->middle_initial,
                    $member->last_name,
                    $member->suffix,
                    $member->contact_number,
                    $member->club?->name ?? '',
                    $member->club?->region?->name ?? '',
                    $member->position?->name ?? '',
                    $member->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import members from CSV.
     */
    public function import(MemberImportRequest $request): RedirectResponse
    {
        $user = request()->user();
        $clubId = $request->integer('club_id');
        $club = Club::findOrFail($clubId);

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        // ── Scoping validation ─────────────────────────────────

        // Club Admin: can only import to their own club
        if ($isClubAdmin && (int) $clubId !== (int) $user->club_id) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'Club admins can only import members into their own club.');
        }

        // Regional Admin: can only import to clubs within their region
        if ($isRegionalAdmin && (int) $club->region_id !== (int) $user->region_id) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'Regional admins can only import members into clubs within their region.');
        }

        // ── Parse CSV ──────────────────────────────────────────
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        // Detect and skip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'The CSV file appears to be empty or invalid.');
        }

        // Normalize headers
        $header = array_map(fn ($h) => trim(mb_strtolower(str_replace(['-', ' '], '_', $h))), $header);

        $expectedHeaders = ['first_name', 'm.i.', 'last_name', 'suffix', 'contact_number', 'club', 'region', 'position', 'status'];
        // Also accept 'middle_initial' instead of 'm.i.'
        $normalizedHeaders = array_map(function ($h) {
            return $h === 'middle_initial' ? 'm.i.' : $h;
        }, $header);

        $missing = array_diff($expectedHeaders, $normalizedHeaders);
        if (!empty($missing)) {
            fclose($handle);
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'CSV is missing required columns: ' . implode(', ', $missing) .
                    '. Expected: First Name, M.I., Last Name, Suffix, Contact Number, Club, Region, Position, Status.');
        }

        // Build column index map
        $colMap = [];
        foreach ($normalizedHeaders as $i => $name) {
            $colMap[$name] = $i;
        }

        // ── Process rows ───────────────────────────────────────
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1; // header was row 1

        $nationalPresidentPosition = Position::where('name', 'National President')->first();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $row = array_map('trim', $row);

            // Skip empty rows
            if (count($row) < 3 || (implode('', $row) === '')) {
                continue;
            }

            $firstName = $row[$colMap['first_name']] ?? '';
            $middleInitial = $row[$colMap['m.i.']] ?? '';
            $lastName = $row[$colMap['last_name']] ?? '';
            $suffix = $row[$colMap['suffix']] ?? '';
            $contactNumber = $row[$colMap['contact_number']] ?? '';
            $clubName = $row[$colMap['club']] ?? '';
            $regionName = $row[$colMap['region']] ?? '';
            $positionName = $row[$colMap['position']] ?? '';
            $status = $row[$colMap['status']] ?? 'active';

            // Validate required fields
            if (empty($firstName) || empty($lastName)) {
                $errors[] = "Row {$rowNumber}: First Name and Last Name are required.";
                continue;
            }

            // ── Club & Region validation per row ────────────────
            $rowClubId = $clubId; // default to the selected club_id
            if ($isSuperAdmin || $isNationalAdmin) {
                // For national-level admins, resolve club by name from CSV
                if (!empty($clubName)) {
                    $resolvedClub = Club::where('name', $clubName)->first();
                    if (!$resolvedClub) {
                        $errors[] = "Row {$rowNumber}: Club '{$clubName}' not found.";
                        continue;
                    }
                    // Verify region matches if provided
                    if (!empty($regionName)) {
                        $resolvedRegion = Region::where('name', $regionName)->first();
                        if (!$resolvedRegion) {
                            $errors[] = "Row {$rowNumber}: Region '{$regionName}' not found.";
                            continue;
                        }
                        if ((int) $resolvedClub->region_id !== (int) $resolvedRegion->id) {
                            $errors[] = "Row {$rowNumber}: Club '{$clubName}' is not in Region '{$regionName}'.";
                            continue;
                        }
                    }
                    $rowClubId = $resolvedClub->id;
                }
            } elseif ($isRegionalAdmin && !empty($clubName)) {
                $resolvedClub = Club::where('name', $clubName)
                    ->where('region_id', $user->region_id)
                    ->first();
                if (!$resolvedClub) {
                    $errors[] = "Row {$rowNumber}: Club '{$clubName}' not found in your region.";
                    continue;
                }
                $rowClubId = $resolvedClub->id;
            }

            // Normalize status
            $statusNormalized = in_array(mb_strtolower($status), ['active', 'inactive']) ? mb_strtolower($status) : 'active';

            // Find position by name
            $position = Position::where('name', $positionName)->first();
            if (!$position) {
                $errors[] = "Row {$rowNumber}: Position '{$positionName}' not found. Skipping.";
                continue;
            }

            // Check for National President restriction
            if ($nationalPresidentPosition && (int) $position->id === (int) $nationalPresidentPosition->id) {
                if ($isClubAdmin || $isRegionalAdmin) {
                    $errors[] = "Row {$rowNumber}: Cannot import a member with 'National President' position.";
                    continue;
                }
            }

            // Check for exact duplicate (same first_name + last_name + contact_number in the same club)
            $duplicate = Member::query()
                ->where('club_id', $rowClubId)
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($firstName))])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($lastName))])
                ->where('contact_number', $contactNumber)
                ->first();

            if ($duplicate) {
                $skipped++;
                continue;
            }

            // Create member
            $member = new Member([
                'club_id' => $rowClubId,
                'position_id' => $position->id,
                'first_name' => $firstName,
                'middle_initial' => $middleInitial ?: null,
                'last_name' => $lastName,
                'suffix' => $suffix ?: null,
                'contact_number' => $contactNumber,
                'status' => $statusNormalized,
            ]);
            $member->applySlugFromName();
            $member->save();

            $imported++;

            // Log each imported member individually
            activity()
                ->performedOn($member)
                ->causedBy(auth()->user())
                ->withProperties(['source' => 'csv_import'])
                ->log('created');
        }

        fclose($handle);

        // ── Log the import batch ───────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => count($errors),
                'club_id' => $clubId,
                'club_name' => $club->name,
            ])
            ->log('imported_members');

        // ── Build response ─────────────────────────────────────
        $message = "Import complete. {$imported} member(s) created, {$skipped} duplicate(s) skipped.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' (and ' . (count($errors) - 5) . ' more errors)';
            }
        }

        $flashType = !empty($errors) ? 'error' : 'success';

        return redirect()
            ->route('admin.members.index')
            ->with($flashType, $message);
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

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'slug' => $member->slug,
            ])
            ->log('deleted');

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
     */
    private function optimizeAndStoreImage(UploadedFile $file, string $directory, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 70): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->decode($file);

        $image->scale(width: $maxWidth, height: $maxHeight);

        $filename = uniqid('img_') . '.webp';
        $path = $directory . '/' . $filename;

        $encoded = $image->encode(new WebpEncoder(quality: $quality));
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    /**
     * Store a certificate file with optimization.
     */
    private function storeCertificateFile(UploadedFile $file): string
    {
        $mime = $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            return $this->optimizeAndStoreImage($file, 'certificates', 1200, 1200, 70);
        }

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
