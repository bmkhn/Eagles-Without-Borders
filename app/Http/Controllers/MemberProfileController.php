<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\View\View;

class MemberProfileController extends Controller
{
    /**
     * Display the public member profile.
     *
     * - Active members are viewable by anyone.
     * - Inactive members are only viewable by authenticated admins (super-admin, national-admin,
     *   regional-admin of the same region, or club-admin of the same club).
     * - Otherwise, redirect to the renewal page.
     */
    public function show(string $slug): View
    {
        $member = Member::query()
            ->with(['position', 'club.region', 'certificates'])
            ->where('slug', $slug)
            ->first();

        if (! $member) {
            return view('public.member-not-found');
        }

        // If member is active, show the profile
        if ($member->status === 'active') {
            return view('public.member-profile', ['member' => $member]);
        }

        // Inactive member — check if the user is authorized
        $user = request()->user();

        if ($user) {
            // Super admin & national admin can view any profile
            if ($user->hasRole('super-admin') || $user->hasRole('national-admin')) {
                return view('public.member-profile', ['member' => $member]);
            }

            // Regional admin can view members in their region
            if ($user->hasRole('regional-admin') && $user->region_id && $member->club) {
                if ((int) $member->club->region_id === (int) $user->region_id) {
                    return view('public.member-profile', ['member' => $member]);
                }
            }

            // Club admin can view members in their club
            if ($user->hasRole('club-admin') && $user->club_id === $member->club_id) {
                return view('public.member-profile', ['member' => $member]);
            }
        }

        // Not authorized — redirect to renewal page
        return view('public.member-renew', ['member' => $member]);
    }
}
