<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Store a new payment (record a member as paid for a year).
     * This is a sensitive operation — requires confirmation and is heavily logged.
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
                Rule::unique('payments', 'year_paid')
                    ->where('member_id', $request->integer('member_id')),
            ],
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

        $payment = Payment::create([
            'member_id' => $member->id,
            'year_paid' => $validated['year_paid'],
            'date_paid' => now(),
        ]);

        // Log with separate payment log name for audit trail
        activity('payment')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withProperties([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'year_paid' => $validated['year_paid'],
                'action' => 'record_payment',
            ])
            ->log('payment_recorded');

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Payment recorded for {$member->name} — Year {$validated['year_paid']}.");
    }

    /**
     * Delete a payment record.
     * Very sensitive — requires double confirmation and heavy logging.
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

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', "Payment record for Year {$year} has been deleted for {$member?->name}.");
    }
}
