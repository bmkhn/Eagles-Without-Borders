<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display a paginated list of payments with filters.
     */
    public function index(): View
    {
        $user = request()->user();

        $filterYear = request()->integer('year');
        $filterClubId = request()->integer('club_id');
        $filterMemberName = request()->string('q')->trim()->toString();

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $paymentsQuery = Payment::query()
            ->with(['member.club.region', 'member.position']);

        // Scope
        if ($isClubAdmin) {
            $paymentsQuery->whereHas('member', fn ($q) => $q->where('club_id', $user->club_id));
        } elseif ($isRegionalAdmin) {
            $paymentsQuery->whereHas('member.club', fn ($q) => $q->where('region_id', $user->region_id));
        }

        if ($filterYear) {
            $paymentsQuery->where('year_paid', $filterYear);
        }

        if ($filterClubId && ($isSuperAdmin || $isNationalAdmin)) {
            $paymentsQuery->whereHas('member', fn ($q) => $q->where('club_id', $filterClubId));
        }

        if ($filterMemberName !== '') {
            $paymentsQuery->whereHas('member', function ($q) use ($filterMemberName) {
                $q->where('first_name', 'like', '%' . $filterMemberName . '%')
                  ->orWhere('last_name', 'like', '%' . $filterMemberName . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $filterMemberName . '%'])
                  ->orWhereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ['%' . str_replace('.', '', $filterMemberName) . '%']);
            });
        }

        $payments = $paymentsQuery->orderByDesc('year_paid')
            ->orderByDesc('date_paid')
            ->paginate(20)
            ->withQueryString();

        $clubs = ($isSuperAdmin || $isNationalAdmin)
            ? Club::query()->orderBy('name')->get()
            : collect();

        $years = range(now()->year, 2020);

        return view('admin.payments.index', [
            'payments' => $payments,
            'clubs' => $clubs,
            'years' => $years,
            'filterYear' => $filterYear,
            'filterClubId' => $filterClubId,
            'filterMemberName' => $filterMemberName,
        ]);
    }

    /**
     * Show the form to record a new payment.
     */
    public function create(): View
    {
        $user = request()->user();

        $isSuperAdmin = $user->hasRole('super-admin');
        $isNationalAdmin = $user->hasRole('national-admin');
        $isRegionalAdmin = $user->hasRole('regional-admin') && $user->region_id;
        $isClubAdmin = $user->hasRole('club-admin') && $user->club_id;

        $membersQuery = Member::query()->with(['club.region', 'position'])->orderBy('last_name');

        if ($isClubAdmin) {
            $membersQuery->where('club_id', $user->club_id);
        } elseif ($isRegionalAdmin) {
            $membersQuery->whereHas('club', fn ($q) => $q->where('region_id', $user->region_id));
        }

        $members = $membersQuery->get();

        // Get all existing payments for these members to detect duplicates client-side
        // Keyed by member_id => array of paid years
        $existingPayments = Payment::whereIn('member_id', $members->pluck('id'))
            ->select('member_id', 'year_paid')
            ->get()
            ->groupBy('member_id')
            ->map(fn ($payments) => $payments->pluck('year_paid')->toArray());

        return view('admin.payments.create', [
            'members' => $members,
            'currentYear' => (int) now()->year,
            'existingPayments' => $existingPayments,
        ]);
    }

    /**
     * Store a new payment (record a member as paid for a year).
     * Auto-updates the member's status based on the current year.
     *
     * If a payment for this member+year was previously soft-deleted, it will
     * be restored instead of creating a duplicate.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'year_paid' => [
                'required',
                'integer',
                'min:2000',
                'max:2099',
            ],
            'date_paid' => ['nullable', 'date'],
        ]);

        $member = Member::with('club')->findOrFail($validated['member_id']);

        // Scope check: ensure the user can access this member
        $user = request()->user();
        if ($user->hasRole('club-admin') && $user->club_id && (int) $member->club_id !== (int) $user->club_id) {
            abort(403, 'You can only record payments for members in your club.');
        }
        if ($user->hasRole('regional-admin') && $user->region_id && $member->club) {
            $memberRegionId = $member->club->region_id;
            if ((int) $memberRegionId !== (int) $user->region_id) {
                abort(403, 'You can only record payments for members in your region.');
            }
        }

        // Check for an active (non-deleted) duplicate first
        $existingPayment = Payment::where('member_id', $validated['member_id'])
            ->where('year_paid', $validated['year_paid'])
            ->first();

        if ($existingPayment) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This member already has a payment recorded for Year ' . $validated['year_paid'] . '.');
        }

        // Check for a soft-deleted payment — restore it instead of creating a new one
        $trashedPayment = Payment::onlyTrashed()
            ->where('member_id', $validated['member_id'])
            ->where('year_paid', $validated['year_paid'])
            ->first();

        if ($trashedPayment) {
            $trashedPayment->restore();
            $trashedPayment->update([
                'date_paid' => $validated['date_paid'] ?? now(),
            ]);
            $trashedPayment->refresh();

            $member->updateStatusFromPayments();

            activity('payment')
                ->performedOn($trashedPayment)
                ->causedBy(auth()->user())
                ->withProperties([
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'year_paid' => $validated['year_paid'],
                    'date_paid' => $trashedPayment->date_paid->format('Y-m-d'),
                    'action' => 'restored_payment',
                ])
                ->log('payment_restored');

            if ($request->has('_redirect')) {
                $redirectTo = $request->input('_redirect');
            } else {
                $redirectTo = route('admin.payments.index');
            }

            return redirect($redirectTo)
                ->with('success', "Payment restored for {$member->name} — Year {$validated['year_paid']}.");
        }

        // No existing or trashed payment — create a brand new one
        $payment = Payment::create([
            'member_id' => $member->id,
            'year_paid' => $validated['year_paid'],
            'date_paid' => $validated['date_paid'] ?? now(),
        ]);

        // Auto-update member status based on current year payment
        $member->updateStatusFromPayments();

        // Log with separate payment log name for audit trail
        activity('payment')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'year_paid' => $validated['year_paid'],
                'date_paid' => $payment->date_paid->format('Y-m-d'),
                'action' => 'record_payment',
            ])
            ->log('payment_recorded');

        // Determine redirect: if _redirect is explicitly provided (e.g. from inline form), use it;
        // otherwise redirect to the payments index (standalone create page).
        if ($request->has('_redirect')) {
            $redirectTo = $request->input('_redirect');
        } else {
            $redirectTo = route('admin.payments.index');
        }

        return redirect($redirectTo)
            ->with('success', "Payment recorded for {$member->name} — Year {$validated['year_paid']}.");
    }

    /**
     * Display a single payment record.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['member.club.region', 'member.position']);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show the form to edit a payment record.
     */
    public function edit(Payment $payment): View
    {
        $payment->load(['member.club.region', 'member.position']);

        return view('admin.payments.edit', [
            'payment' => $payment,
        ]);
    }

    /**
     * Update a payment record (year_paid and/or date_paid).
     * Re-evaluates the member's status after the update.
     *
     * Includes soft-deleted records in the uniqueness check to prevent
     * conflicts with payments that were previously deleted.
     */
    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'year_paid' => [
                'required',
                'integer',
                'min:2000',
                'max:2099',
            ],
            'date_paid' => ['nullable', 'date'],
        ]);

        // Check uniqueness including soft-deleted records (excluding this payment)
        $exists = Payment::withTrashed()
            ->where('member_id', $payment->member_id)
            ->where('year_paid', $validated['year_paid'])
            ->where('id', '!=', $payment->id)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This member already has a payment recorded for Year ' . $validated['year_paid'] . '.');
        }

        $oldYear = $payment->year_paid;
        $oldDate = $payment->date_paid?->format('Y-m-d');

        $payment->update([
            'year_paid' => $validated['year_paid'],
            'date_paid' => $validated['date_paid'] ?? $payment->date_paid,
        ]);

        // Re-evaluate member status (the year may have changed)
        $member = $payment->member;
        $member->updateStatusFromPayments();

        activity('payment')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'changes' => [
                    'year_paid' => ['old' => $oldYear, 'new' => $validated['year_paid']],
                    'date_paid' => ['old' => $oldDate, 'new' => $validated['date_paid'] ?? $payment->date_paid->format('Y-m-d')],
                ],
                'action' => 'update_payment',
            ])
            ->log('payment_updated');

        // Determine redirect: if _redirect is explicitly provided, use it;
        // otherwise redirect to the member edit page (inline edit) or payments index (standalone edit)
        if ($request->has('_redirect')) {
            $redirectTo = $request->input('_redirect');
        } else {
            $redirectTo = route('admin.payments.index');
        }

        return redirect($redirectTo)
            ->with('success', "Payment updated for {$member->name} — Year {$validated['year_paid']}.");
    }

    /**
     * Delete a payment record.
     * Re-evaluates the member's status after deletion.
     */
    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        // Extra confirmation checks
        $request->validate([
            'confirm_delete' => ['required', 'accepted'],
            'confirm_text' => ['required', 'string', 'in:DELETE'],
        ]);

        $member = $payment->member;
        $year = $payment->year_paid;

        activity('payment')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member?->id,
                'member_name' => $member?->name,
                'year_paid' => $year,
                'action' => 'delete_payment',
            ])
            ->log('payment_deleted');

        $payment->delete();

        // Re-evaluate member status (may have lost current year payment)
        if ($member) {
            $member->updateStatusFromPayments();
        }

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Payment record for Year {$year} has been deleted for {$member?->name}.");
    }
}
