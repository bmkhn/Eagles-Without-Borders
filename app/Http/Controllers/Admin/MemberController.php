<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberImportRequest;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Http\Requests\Admin\MemberUpdateRequest;
use App\Models\Certificate;
use App\Models\Club;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Position;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MemberController extends Controller
{
    /**
     * Whitelisted sort options for the member index (key => display label).
     */
    public const SORT_OPTIONS = [
        'last_name_asc' => 'Last Name (A–Z)',
        'last_name_desc' => 'Last Name (Z–A)',
        'first_name_asc' => 'First Name (A–Z)',
        'first_name_desc' => 'First Name (Z–A)',
        'club' => 'Club',
        'region' => 'Region',
        'position' => 'Position',
        'status' => 'Status',
        'created_at_desc' => 'Newest first',
        'created_at' => 'Oldest first',
    ];

    public function index(): View|RedirectResponse
    {
        $user = request()->user();

        // Remember the current index state (search, filters, sort, page) so that
        // create/update/import/delete redirects can return to the same view.
        session(['admin.members.index.query' => request()->query()]);

        $q = request()->string('q')->trim()->toString();
        $filterRegionId = request()->integer('region_id');
        $filterClubId = request()->integer('club_id');
        $filterStatus = request()->string('status')->trim()->toString();
        $filterPositionId = request()->integer('position_id');
        $filterPhoto = request()->string('photo')->trim()->toString();
        $sortBy = $this->normalizeSort(request()->string('sort')->trim()->toString());

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

        if ($filterPhoto === 'missing') {
            $membersQuery->where(function ($query) {
                $query->whereNull('profile_picture')->orWhere('profile_picture', '');
            });
        } elseif ($filterPhoto === 'has') {
            $membersQuery->whereNotNull('profile_picture')->where('profile_picture', '!=', '');
        }

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $q . '%'])
                    ->orWhereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ['%' . str_replace('.', '', $q) . '%'])
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

        $this->applySort($membersQuery, $sortBy);

        $members = $membersQuery->paginate(10)->withQueryString();

        // If the requested page no longer exists (e.g. returning after an edit
        // changed the result count), fall back to the last available page so the
        // user is never dropped onto an empty page.
        if ($members->isEmpty() && $members->currentPage() > 1) {
            return redirect()->route('admin.members.index', array_merge(request()->query(), [
                'page' => $members->lastPage(),
            ]));
        }

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

        // Resolve the region name for scoped admins
        $userRegionName = null;
        if ($isRegionalAdmin && $user->region_id) {
            $region = \App\Models\Region::find($user->region_id);
            $userRegionName = $region?->name;
        } elseif ($isClubAdmin && $user->club_id) {
            $club = \App\Models\Club::with('region')->find($user->club_id);
            $userRegionName = $club?->region?->name;
        }

        return view('admin.members.index', [
            'members' => $members,
            'q' => $q,
            'sortBy' => $sortBy,
            'sortOptions' => self::SORT_OPTIONS,
            'filterRegionId' => $filterRegionId,
            'filterClubId' => $filterClubId,
            'filterStatus' => $filterStatus,
            'filterPositionId' => $filterPositionId,
            'filterPhoto' => $filterPhoto,
            'regions' => $regions,
            'clubs' => $clubs,
            'positions' => $positions,
            'totalCount' => $totalCount,
            'unfilteredTotal' => $unfilteredTotal,
            'isClubAdmin' => $isClubAdmin,
            'isSuperAdmin' => $isSuperAdmin,
            'isNationalAdmin' => $isNationalAdmin,
            'isRegionalAdmin' => $isRegionalAdmin,
            'userRegionName' => $userRegionName,
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
            'indexQuery' => session('admin.members.index.query', []),
        ]);
    }

    /**
     * Live duplicate check for the member create form.
     *
     * Returns any members (active OR trashed) with the same first + last name,
     * scoped to what the current user can see, so the form can disable
     * conflicting clubs and warn the user.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $firstName = trim((string) $request->string('first_name'));
        $lastName = trim((string) $request->string('last_name'));
        $ignore = (int) $request->integer('ignore');

        if (mb_strlen($firstName) < 2 || mb_strlen($lastName) < 2) {
            return response()->json(['matches' => []]);
        }

        $user = $request->user();

        $query = Member::withTrashed()
            ->with(['club.region', 'position'])
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower($firstName)])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower($lastName)]);

        // When editing, exclude the member being edited so its own record
        // never shows up as a "duplicate".
        if ($ignore > 0) {
            $query->where('id', '!=', $ignore);
        }

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        if ($isClubAdmin) {
            $query->where('club_id', $user->club_id);
        } elseif ($isRegionalAdmin) {
            $query->whereHas('club', fn ($q) => $q->where('region_id', $user->region_id));
        }

        $matches = $query->orderBy('club_id')->get()->map(fn (Member $m) => [
            'club_id' => $m->club_id !== null ? (int) $m->club_id : null,
            'club_name' => $m->club?->name,
            'region_name' => $m->club?->region?->name,
            'position_name' => $m->position?->name,
            'name' => $m->name,
            'trashed' => $m->trashed(),
        ])->values();

        return response()->json(['matches' => $matches]);
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $data = $request->safe()->except(['profile_picture', 'certificates']);
        // Contact number is optional; store an empty string so NOT NULL column is satisfied.
        $data['contact_number'] = $data['contact_number'] ?? '';

        if ($isClubAdmin) {
            $data['club_id'] = $user->club_id;
        }

        // Rule: duplicates are allowed, but never in the same club.
        // This check includes soft-deleted (trashed) members so recreating
        // a trashed member in their old club is also blocked.
        $duplicate = $this->findSameClubDuplicate((int) $data['club_id'], (string) $data['first_name'], (string) $data['last_name']);

        if ($duplicate) {
            return $this->duplicateConflictRedirect($duplicate);
        }

        $member = new Member($data);
        $member->applySlugFromName();
        $member->status = 'inactive'; // New members start as inactive until they pay

        if ($request->hasFile('profile_picture')) {
            $member->profile_picture = $this->storeProfilePicture($request->file('profile_picture'));
        }

        try {
            $member->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Safety net (e.g. concurrent submissions): never surface a 500 for this.
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }

            return back()
                ->withErrors(['first_name' => 'A member with this name already exists in the selected club and could not be created.'])
                ->withInput();
        }

        if ($request->has('certificates')) {
            $this->syncCertificates($member, $request);
        }

        // Record payments if provided
        $paymentsRecorded = 0;
        if ($request->has('payments')) {
            $existingYears = Payment::where('member_id', $member->id)->pluck('year_paid')->toArray();

            foreach ($request->input('payments', []) as $paymentData) {
                $yearPaid = (int) ($paymentData['year_paid'] ?? 0);

                if ($yearPaid < 2000 || $yearPaid > 2099) {
                    continue;
                }

                // Skip if already exists for this member+year
                if (in_array($yearPaid, $existingYears)) {
                    continue;
                }

                $datePaid = !empty($paymentData['date_paid'])
                    ? \Carbon\Carbon::parse($paymentData['date_paid'])
                    : now();

                Payment::create([
                    'member_id' => $member->id,
                    'year_paid' => $yearPaid,
                    'date_paid' => $datePaid,
                ]);

                $existingYears[] = $yearPaid;
                $paymentsRecorded++;

                activity('payment')
                    ->performedOn($member)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'member_id' => $member->id,
                        'member_name' => $member->name,
                        'year_paid' => $yearPaid,
                        'date_paid' => $datePaid->format('Y-m-d'),
                        'source' => 'member_creation',
                    ])
                    ->log('payment_recorded');
            }

            if ($paymentsRecorded > 0) {
                $member->updateStatusFromPayments();
            }
        }

        $member->load('club.region');

        $logProperties = [
            'member_id' => $member->id,
            'member_name' => $member->name,
            'club' => $member->club?->name,
            'region' => $member->club?->region?->name,
            'position' => $member->position?->name,
            'status' => $member->status,
            'contact_number' => $member->contact_number,
            'source' => 'manual_create',
        ];

        if ($paymentsRecorded > 0) {
            $logProperties['payments_recorded'] = $paymentsRecorded;
        }

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties($logProperties)
            ->log('created');

        $successMessage = $paymentsRecorded > 0
            ? 'Member created successfully with ' . $paymentsRecorded . ' payment(s) recorded.'
            : 'Member created successfully.';

        return $this->redirectToIndex(['success' => $successMessage]);
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
            'indexQuery' => session('admin.members.index.query', []),
        ]);
    }

    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        $user = request()->user();

        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        // Capture original values for audit diff
        $original = [
            'first_name' => $member->getOriginal('first_name'),
            'middle_initial' => $member->getOriginal('middle_initial'),
            'last_name' => $member->getOriginal('last_name'),
            'suffix' => $member->getOriginal('suffix'),
            'club_id' => $member->getOriginal('club_id'),
            'position_id' => $member->getOriginal('position_id'),
            'contact_number' => $member->getOriginal('contact_number'),
        ];

        $data = $request->safe()->except(['profile_picture', 'remove_photo', 'certificates']);
        // Contact number is optional; store an empty string so NOT NULL column is satisfied.
        $data['contact_number'] = $data['contact_number'] ?? '';

        // Status is auto-managed based on payments — do not allow manual changes
        unset($data['status']);

        if ($isClubAdmin) {
            $data['club_id'] = $user->club_id;
        }

        $member->fill($data);
        $member->applySlugFromName();

        // Rule: duplicates are allowed, but never in the same club (same as create).
        // Excludes this member, so saving without changing name/club never conflicts.
        $duplicate = $this->findSameClubDuplicate((int) $member->club_id, (string) $member->first_name, (string) $member->last_name, $member->id);

        if ($duplicate) {
            return $this->duplicateConflictRedirect($duplicate, 'update');
        }

        if ($request->hasFile('profile_picture')) {
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }
            $member->profile_picture = $this->storeProfilePicture($request->file('profile_picture'));
        } elseif ($request->boolean('remove_photo') && $member->profile_picture) {
            Storage::disk('public')->delete($member->profile_picture);
            $member->profile_picture = null;
        }

        try {
            $member->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Safety net (e.g. concurrent edits): never surface a 500 for this.
            // The profile picture was already replaced before save — clean up the new file.
            if ($request->hasFile('profile_picture') && $member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }

            return back()
                ->withErrors(['first_name' => 'Another member with this name already exists in the selected club and the changes could not be saved.'])
                ->withInput();
        }

        if ($request->has('certificates') || $request->boolean('certificates_managed')) {
            $this->syncCertificates($member, $request);
        }

        $member->load('club.region');

        $newValues = [
            'first_name' => $member->first_name,
            'middle_initial' => $member->middle_initial,
            'last_name' => $member->last_name,
            'suffix' => $member->suffix,
            'club_id' => $member->club_id,
            'position_id' => $member->position_id,
            'contact_number' => $member->contact_number,
        ];

        $changes = [];
        foreach ($newValues as $key => $newVal) {
            $oldVal = $original[$key] ?? null;
            if ((string) $oldVal !== (string) $newVal) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        if ($request->hasFile('profile_picture')) {
            $changes['profile_picture'] = ['old' => '(previous)', 'new' => '(replaced)'];
        } elseif ($request->boolean('remove_photo')) {
            $changes['profile_picture'] = ['old' => '(had photo)', 'new' => '(removed)'];
        }

        // ── Enrich properties with old/new names when club changes ──
        $clubChanged = isset($changes['club_id']);
        $extraProperties = [];
        if ($clubChanged) {
            $oldClubId = $original['club_id'];
            $oldClub = $oldClubId ? Club::find($oldClubId) : null;
            $extraProperties['old_club'] = $oldClub?->name;
            $extraProperties['new_club'] = $member->club?->name;
        }

        // ── Log single update event (seen by both sender and receiver via scoping) ──
        if (!empty($changes)) {
            activity()
                ->performedOn($member)
                ->causedBy(auth()->user())
                ->withProperties(array_merge([
                    'changes' => $changes,
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'club' => $member->club?->name,
                    'region' => $member->club?->region?->name,
                    'position' => $member->position?->name,
                    'status' => $member->status,
                    'contact_number' => $member->contact_number,
                ], $extraProperties))
                ->log('updated');
        }

        return $this->redirectToIndex(['success' => 'Member updated successfully.']);
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
        $filterPhoto = request()->string('photo')->trim()->toString();

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

        $sortBy = $this->normalizeSort(request()->string('sort')->trim()->toString());

        $membersQuery = Member::query()
            ->with(['club.region', 'position', 'payments']);

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

        if ($filterPhoto === 'missing') {
            $membersQuery->where(function ($query) {
                $query->whereNull('profile_picture')->orWhere('profile_picture', '');
            });
        } elseif ($filterPhoto === 'has') {
            $membersQuery->whereNotNull('profile_picture')->where('profile_picture', '!=', '');
        }

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $q . '%'])
                    ->orWhereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ['%' . str_replace('.', '', $q) . '%'])
                    ->orWhere('contact_number', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $this->applySort($membersQuery, $sortBy);

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
                    'photo' => $filterPhoto ?: null,
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
            fputcsv($handle, ['First Name', 'M.I.', 'Last Name', 'Suffix', 'Contact Number', 'Club', 'Region', 'Position', 'Status', 'Paid Years']);

            if ($members->isEmpty()) {
                $this->writeExampleCsvRow($handle);
            } else {
                foreach ($members as $member) {
                    $paidEntries = $member->payments->sortBy('year_paid')->map(function ($p) {
                        return $p->year_paid . ':' . $p->date_paid->format('Y-m-d');
                    })->implode(', ');
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
                        $paidEntries,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import members from CSV.
     *
     * CSV must match the export format: First Name, M.I., Last Name, Suffix,
     * Club, Region, Position, Status. Contact Number is optional.
     *
     * - The club is resolved from the CSV 'Club' column — no target club picker needed.
     * - Club Admin: all rows must reference their club.
     * - Regional Admin: all rows must reference clubs within their region.
     * - Super/National Admin: club is resolved by name; region is validated if provided.
     */
    public function import(MemberImportRequest $request): RedirectResponse
    {
        $user = request()->user();

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;

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
            return $this->redirectToIndex(['error' => 'The CSV file appears to be empty or invalid.']);
        }

        // Normalize headers
        $header = array_map(fn ($h) => trim(mb_strtolower(str_replace(['-', ' '], '_', $h))), $header);

        // contact_number is optional: rows without it are imported with an empty number.
        $expectedHeaders = ['first_name', 'm.i.', 'last_name', 'suffix', 'club', 'region', 'position', 'status', 'paid_years'];
        // Also accept 'middle_initial' instead of 'm.i.'
        $normalizedHeaders = array_map(function ($h) {
            return $h === 'middle_initial' ? 'm.i.' : $h;
        }, $header);

        $missing = array_diff($expectedHeaders, $normalizedHeaders);
        if (!empty($missing)) {
            fclose($handle);
            return $this->redirectToIndex(['error' => 'CSV is missing required columns: ' . implode(', ', $missing) .
                '. Expected: First Name, M.I., Last Name, Suffix, Club, Region, Position, Status, Paid Years. Contact Number is optional.']);
        }

        // Build column index map
        $colMap = [];
        foreach ($normalizedHeaders as $i => $name) {
            $colMap[$name] = $i;
        }

        // ── Read all rows into memory once ────────────────────────────────
        $allRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            // Skip empty rows
            if (count($row) < 3 || (implode('', $row) === '')) {
                continue;
            }
            $allRows[] = $row;
        }
        fclose($handle);

        // ── Collect club names from all rows for scope validation ────────
        $csvClubNames = [];
        foreach ($allRows as $row) {
            $clubName = $row[$colMap['club']] ?? '';
            if (!empty($clubName) && !in_array($clubName, $csvClubNames)) {
                $csvClubNames[] = $clubName;
            }
        }

        // ── Scope validation (Club Admin & Regional Admin) ─────────────────
        if ($isClubAdmin) {
            $userClub = Club::find($user->club_id);
            $userClubName = $userClub?->name;
            foreach ($csvClubNames as $csvClubName) {
                if ($csvClubName !== $userClubName) {
                    return $this->redirectToIndex(['error' => "Club admins can only import members into their own club ('{$userClubName}'). The CSV references '{$csvClubName}'."]);
                }
            }
        }

        if ($isRegionalAdmin) {
            $userRegion = Region::find($user->region_id);
            $regionClubNames = Club::where('region_id', $user->region_id)->pluck('name')->all();
            foreach ($csvClubNames as $csvClubName) {
                if (!in_array($csvClubName, $regionClubNames)) {
                    return $this->redirectToIndex(['error' => "Regional admins can only import members into clubs within their region ('{$userRegion?->name}'). The CSV references '{$csvClubName}' which is not in your region."]);
                }
            }
        }

        // ── Process rows ───────────────────────────────────────
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1; // header was row 1

        $nationalPresidentPosition = Position::where('name', 'National President')->first();

        foreach ($allRows as $row) {
            $rowNumber++;

            $firstName = $row[$colMap['first_name']] ?? '';
            $middleInitial = $row[$colMap['m.i.']] ?? '';
            $lastName = $row[$colMap['last_name']] ?? '';
            $suffix = $row[$colMap['suffix']] ?? '';
            // Contact Number column is optional; guard the index so missing columns don't emit warnings.
            $contactNumber = isset($colMap['contact_number']) ? ($row[$colMap['contact_number']] ?? '') : '';
            $clubName = $row[$colMap['club']] ?? '';
            $regionName = $row[$colMap['region']] ?? '';
            $positionName = $row[$colMap['position']] ?? '';

            // Validate required fields
            if (empty($firstName) || empty($lastName)) {
                $errors[] = "Row {$rowNumber}: First Name and Last Name are required.";
                continue;
            }

            // ── Resolve position FIRST (needed for National President club check) ──
            $position = Position::where('name', $positionName)->first();
            if (!$position) {
                $errors[] = "Row {$rowNumber}: Position '{$positionName}' not found. Skipping.";
                continue;
            }

            $isNationalPresident = $nationalPresidentPosition && (int) $position->id === (int) $nationalPresidentPosition->id;

            // ── National President: no club, skip club requirement ──
            if ($isNationalPresident) {
                if ($isClubAdmin || $isRegionalAdmin) {
                    $errors[] = "Row {$rowNumber}: Cannot import a member with 'National President' position.";
                    continue;
                }
                // Super Admin / National Admin can create National President without a club
                $resolvedClub = null;
            } else {
                // ── Resolve club from CSV ──────────────────────────────
                if (empty($clubName)) {
                    $errors[] = "Row {$rowNumber}: Club is required.";
                    continue;
                }

                $resolvedClub = Club::where('name', $clubName)->first();
                if (!$resolvedClub) {
                    $errors[] = "Row {$rowNumber}: Club '{$clubName}' not found.";
                    continue;
                }

                // Verify region matches if provided (Super/National Admin)
                if (!empty($regionName) && ($isSuperAdmin || $isNationalAdmin)) {
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
            }

            // ── Parse paid years (format: "2024:2024-01-15, 2025:2025-03-01" or just "2024") ──
            $paidYearsRaw = $row[$colMap['paid_years']] ?? '';
            $paidEntries = [];
            if (!empty(trim($paidYearsRaw))) {
                $parts = explode(',', $paidYearsRaw);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;

                    if (str_contains($part, ':')) {
                        [$yearStr, $dateStr] = explode(':', $part, 2);
                        $yearStr = trim($yearStr);
                        $dateStr = trim($dateStr);
                        if (is_numeric($yearStr) && (int) $yearStr >= 2000 && (int) $yearStr <= 2099) {
                            $date = \Carbon\Carbon::canBeCreatedFromFormat($dateStr, 'Y-m-d') ? \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr) : null;
                            $paidEntries[] = [
                                'year' => (int) $yearStr,
                                'date' => $date ? $date->format('Y-m-d') : now()->format('Y-m-d'),
                            ];
                        }
                    } else {
                        $year = trim($part);
                        if (is_numeric($year) && (int) $year >= 2000 && (int) $year <= 2099) {
                            $paidEntries[] = [
                                'year' => (int) $year,
                                'date' => now()->format('Y-m-d'),
                            ];
                        }
                    }
                }
            }

            // ── Check for existing member ──
            if ($isNationalPresident) {
                // Only one National President allowed (unique by position, no club)
                $existingNP = Member::query()
                    ->where('position_id', $position->id)
                    ->whereNull('club_id')
                    ->first();

                if ($existingNP) {
                    $skipped++;
                    continue;
                }
            } else {
                // Check for exact duplicate in the same club. When the CSV provides a
                // contact number, match on name + contact number; otherwise fall back to
                // name-only matching (same rule as manual create/update).
                $duplicateQuery = Member::query()
                    ->where('club_id', $resolvedClub->id)
                    ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($firstName))])
                    ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($lastName))]);

                if ($contactNumber !== '') {
                    $duplicateQuery->where('contact_number', $contactNumber);
                }

                $duplicate = $duplicateQuery->first();

                if ($duplicate) {
                    $skipped++;
                    continue;
                }
            }

            // Create member (always starts as inactive; status is auto-managed by payments)
            $memberData = [
                'position_id' => $position->id,
                'first_name' => $firstName,
                'middle_initial' => $middleInitial ?: null,
                'last_name' => $lastName,
                'suffix' => $suffix ?: null,
                'contact_number' => $contactNumber,
                'status' => 'inactive',
            ];

            if ($isNationalPresident) {
                $memberData['club_id'] = null;
            } else {
                $memberData['club_id'] = $resolvedClub->id;
            }

            $member = new Member($memberData);
            $member->applySlugFromName();

            try {
                $member->save();
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $skipped++;
                continue;
            }

            // ── Create payment records for paid years ──────────────────
            if (!empty($paidEntries)) {
                $existingYears = $member->payments()->pluck('year_paid')->all();
                foreach ($paidEntries as $entry) {
                    if (!in_array($entry['year'], $existingYears)) {
                        $member->payments()->create([
                            'year_paid' => $entry['year'],
                            'date_paid' => $entry['date'],
                        ]);
                    }
                }

                // Re-evaluate status — if current year is among paid years, mark as active
                $member->updateStatusFromPayments();
            }

            $imported++;

            // Log each imported member individually
            activity()
                ->performedOn($member)
                ->causedBy(auth()->user())
                ->withProperties(['source' => 'csv_import'])
                ->log('created');
        }

        // ── Log the import batch ───────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => count($errors),
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

        return $this->redirectToIndex([$flashType => $message]);
    }

    /**
     * Display soft-deleted (trashed) members.
     */
    public function trashed(): View
    {
        $user = request()->user();

        $q = request()->string('q')->trim()->toString();
        $filterRegionId = request()->integer('region_id');
        $filterClubId = request()->integer('club_id');
        $filterPositionId = request()->integer('position_id');

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $membersQuery = Member::onlyTrashed()
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

        if ($filterPositionId) {
            $membersQuery->where('position_id', $filterPositionId);
        }

        if ($q !== '') {
            $membersQuery->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $q . '%'])
                    ->orWhereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ['%' . str_replace('.', '', $q) . '%'])
                    ->orWhere('contact_number', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $trashedMembers = $membersQuery->orderByDesc('deleted_at')
            ->paginate(10)->withQueryString();

        $trashedCount = Member::onlyTrashed()->count();

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

        $positionsQuery = Position::query()->orderBy('name');
        if ($isClubAdmin || $isRegionalAdmin) {
            $positionsQuery->where('name', '!=', 'National President');
        }
        $positions = $positionsQuery->get();

        // Resolve the region name for scoped admins
        $userRegionName = null;
        if ($isRegionalAdmin && $user->region_id) {
            $region = \App\Models\Region::find($user->region_id);
            $userRegionName = $region?->name;
        } elseif ($isClubAdmin && $user->club_id) {
            $club = \App\Models\Club::find($user->club_id);
            $userRegionName = $club?->region?->name;
        }

        return view('admin.members.trashed', [
            'trashedMembers' => $trashedMembers,
            'q' => $q,
            'filterRegionId' => $filterRegionId,
            'filterClubId' => $filterClubId,
            'filterPositionId' => $filterPositionId,
            'trashedCount' => $trashedCount,
            'regions' => $regions,
            'clubs' => $clubsQuery->get(),
            'positions' => $positions,
            'isSuperAdmin' => $isSuperAdmin,
            'isNationalAdmin' => $isNationalAdmin,
            'isRegionalAdmin' => $isRegionalAdmin,
            'isClubAdmin' => $isClubAdmin,
            'userRegionName' => $userRegionName,
        ]);
    }

    /**
     * Restore a soft-deleted member along with their certificates and payments.
     */
    public function restore(int $id): RedirectResponse
    {
        $member = Member::onlyTrashed()->findOrFail($id);

        // Scope check
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only restore members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only restore members in your region.');
            }
        }

        // Restore related records too
        Certificate::where('member_id', $member->id)->restore();
        Payment::where('member_id', $member->id)->restore();

        $member->restore();

        // If another (non-deleted) member already occupies this slug (taken while
        // this member was trashed), regenerate a fresh unique one so the public
        // profile URL stays unambiguous.
        $slugTaken = Member::query()
            ->where('slug', $member->slug)
            ->where('id', '!=', $member->id)
            ->exists();

        if ($slugTaken) {
            $member->applySlugFromName();
            $member->save();
        }

        // Re-evaluate status after restoring
        $member->updateStatusFromPayments();

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'slug' => $member->slug,
            ])
            ->log('restored');

        return redirect()
            ->route('admin.members.trashed')
            ->with('success', "Member '{$member->name}' restored successfully.");
    }

    public function destroy(Member $member): RedirectResponse
    {
        // Soft-delete related records (keep files on disk for potential restoration)
        foreach ($member->certificates as $cert) {
            $cert->delete(); // soft delete only, keep files
        }

        foreach ($member->payments as $payment) {
            $payment->delete(); // soft delete
        }

        // Keep profile picture on disk for potential restoration

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'slug' => $member->slug,
            ])
            ->log('deleted');

        $member->delete(); // soft delete

        return $this->redirectToIndex(['success' => 'Member moved to trash.']);
    }

    /**
     * Permanently delete a trashed member, their certificates (files + records), and payments.
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        try {
            $member = Member::onlyTrashed()->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('admin.members.trashed')
                ->with('error', 'Member not found. It may have already been permanently deleted.');
        }

        // Scope check
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only force-delete members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only force-delete members in your region.');
            }
        }

        // Permanently delete certificate files and records
        $certificates = Certificate::where('member_id', $member->id)->withTrashed()->get();
        foreach ($certificates as $cert) {
            if ($cert->file) {
                Storage::disk('public')->delete($cert->file);
            }
            $cert->forceDelete();
        }

        // Permanently delete payments
        Payment::where('member_id', $member->id)->withTrashed()->forceDelete();

        // Delete profile picture
        if ($member->profile_picture) {
            Storage::disk('public')->delete($member->profile_picture);
        }

        $name = $member->name;

        activity()
            ->performedOn($member)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'slug' => $member->slug,
            ])
            ->log('force_deleted');

        $member->forceDelete();

        return redirect()
            ->route('admin.members.trashed')
            ->with('success', "Member '{$name}' permanently deleted.");
    }

    /**
     * Download a sample CSV showing the expected format for imports.
     * Always outputs the example row regardless of database state.
     */
    public function sampleCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="members-sample-import-format.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['First Name', 'M.I.', 'Last Name', 'Suffix', 'Contact Number', 'Club', 'Region', 'Position', 'Status', 'Paid Years']);
            $this->writeExampleCsvRow($handle);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Write the example CSV row onto an open file handle.
     * Used by both the empty export fallback and the standalone sample download.
     */
    private function writeExampleCsvRow($handle): void
    {
        fputcsv($handle, ['# ── FORMAT EXAMPLE ONLY: Replace with actual data before importing ──']);
        fputcsv($handle, [
            'Juan',           // First Name
            'D',              // M.I.
            'Dela Cruz',      // Last Name
            'Jr.',            // Suffix
            '09171234567',    // Contact Number
            'Your Club Name', // Club (use exact club name as it appears in the system)
            'Your Region',    // Region (use exact region name as it appears in the system)
            'Member',         // Position (use exact position name as it appears in the system)
            'active',         // Status (active or inactive)
            '2024:2024-01-15, 2025:2025-03-01', // Paid Years (format: YEAR:YYYY-MM-DD, YEAR:YYYY-MM-DD)
        ]);
    }

    /**
     * Find another member (active or trashed) with the same first + last name
     * in the same club. Enforces the "duplicates allowed, but never in the
     * same club" rule on create and update.
     */
    private function findSameClubDuplicate(int $clubId, string $firstName, string $lastName, ?int $ignoreMemberId = null): ?Member
    {
        return Member::withTrashed()
            ->when($ignoreMemberId, fn ($q) => $q->where('id', '!=', $ignoreMemberId))
            ->where('club_id', $clubId)
            ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($firstName))])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($lastName))])
            ->with(['club.region', 'position'])
            ->first();
    }

    /**
     * Build the friendly error redirect for a same-club duplicate conflict.
     * $action controls the verb in the message ("create" vs "update").
     */
    private function duplicateConflictRedirect(Member $duplicate, string $action = 'create'): RedirectResponse
    {
        $duplicateName = $duplicate->name;
        $verb = $action === 'create' ? 'create' : 'save';

        if ($duplicate->trashed()) {
            $trashedClub = $duplicate->club?->name ?? 'an unknown club';

            return back()
                ->withErrors(['first_name' => "A member named '{$duplicateName}' already exists but is in the trash (previously in {$trashedClub}). You cannot {$verb} a member with the same name in the same club."])
                ->withInput();
        }

        $location = collect([$duplicate->club?->region?->name, $duplicate->club?->name])
            ->filter()
            ->implode(' · ');
        $position = $duplicate->position?->name ?? 'an unknown position';

        return back()
            ->withErrors(['first_name' => "A member named '{$duplicateName}' already has a profile in {$location} as {$position}. You cannot {$verb} a member with the same name in the same club."])
            ->withInput();
    }

    /**
     * Redirect back to the member index, preserving the last seen index state
     * (search, filters, sort, and page) so the user isn't dropped onto a fresh list.
     */
    private function redirectToIndex(array $with = []): RedirectResponse
    {
        return redirect()
            ->route('admin.members.index', session('admin.members.index.query', []))
            ->with($with);
    }

    /**
     * Normalize the requested sort key against the whitelist of allowed options.
     */
    private function normalizeSort(string $sort): string
    {
        // Legacy keys from before the sort was split into separate last/first name options.
        if ($sort === 'name') {
            return 'last_name_asc';
        }

        if ($sort === 'name_desc') {
            return 'last_name_desc';
        }

        return array_key_exists($sort, self::SORT_OPTIONS) ? $sort : 'last_name_asc';
    }

    /**
     * Apply the requested ordering to the members query. Related-column sorts
     * (club, region, position) use left joins so members without those records
     * (e.g. the National President) are still included.
     */
    private function applySort($query, string $sort): void
    {
        switch ($sort) {
            case 'last_name_asc':
                $query->orderBy('members.last_name')->orderBy('members.first_name');
                break;
            case 'last_name_desc':
                $query->orderByDesc('members.last_name')->orderByDesc('members.first_name');
                break;
            case 'first_name_asc':
                $query->orderBy('members.first_name')->orderBy('members.last_name');
                break;
            case 'first_name_desc':
                $query->orderByDesc('members.first_name')->orderByDesc('members.last_name');
                break;
            case 'club':
                $query->leftJoin('clubs', 'clubs.id', '=', 'members.club_id')
                    ->select('members.*')
                    ->orderBy('clubs.name')->orderBy('members.last_name')->orderBy('members.first_name');
                break;
            case 'region':
                $query->leftJoin('clubs', 'clubs.id', '=', 'members.club_id')
                    ->leftJoin('regions', 'regions.id', '=', 'clubs.region_id')
                    ->select('members.*')
                    ->orderBy('regions.name')->orderBy('members.last_name')->orderBy('members.first_name');
                break;
            case 'position':
                $query->leftJoin('positions', 'positions.id', '=', 'members.position_id')
                    ->select('members.*')
                    ->orderBy('positions.name')->orderBy('members.last_name')->orderBy('members.first_name');
                break;
            case 'status':
                $query->orderBy('members.status')->orderBy('members.last_name')->orderBy('members.first_name');
                break;
            case 'created_at':
                $query->orderBy('members.created_at');
                break;
            case 'created_at_desc':
                $query->orderByDesc('members.created_at');
                break;
            default: // 'last_name_asc'
                $query->orderBy('members.last_name')->orderBy('members.first_name');
        }
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
     * Verifies the file actually landed on disk before returning its path so a
     * failed write (the public disk is configured with throw=false) can never
     * leave the database pointing at an image that does not exist.
     */
    private function optimizeAndStoreImage(UploadedFile $file, string $directory, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 70): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->decode($file);

            $image->scale(width: $maxWidth, height: $maxHeight);

            $encoded = $image->encode(new WebpEncoder(quality: $quality));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'profile_picture' => 'The image could not be processed. Please try a different file.',
            ]);
        }

        $filename = uniqid('img_') . '.webp';
        $path = $directory . '/' . $filename;

        $disk = Storage::disk('public');
        $written = $disk->put($path, $encoded);

        if (!$written || !$disk->fileExists($path)) {
            $disk->delete($path);

            throw ValidationException::withMessages([
                'profile_picture' => 'The image could not be saved. Please try again.',
            ]);
        }

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
