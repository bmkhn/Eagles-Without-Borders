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
     * - Inactive members are only viewable by authenticated National Presidents
     *   or Club Presidents of the same club.
     * - Otherwise, redirect to the renewal page.
     */
    public function show(string $slug): View
    {
        $member = Member::query()
            ->with(['position', 'club.region', 'certificates'])
            ->where('slug', $slug)
            ->firstOrFail();

        // If member is active, show the profile
        if ($member->status === 'active') {
            return view('public.member-profile', ['member' => $member]);
        }

        // Inactive member — check if the user is authorized
        $user = request()->user();

        if ($user && ($user->hasRole('national-president') ||
            ($user->hasRole('club-president') && $user->club_id === $member->club_id))) {
            return view('public.member-profile', ['member' => $member]);
        }

        // Not authorized — redirect to renewal page
        return view('public.member-renew', ['member' => $member]);
    }
}
