<?php

namespace App\Http\Middleware;

use App\Models\Certificate;
use App\Models\Club;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // super-admin and national-admin bypass all scoping
        if ($user->hasAnyRole(['super-admin', 'national-admin'])) {
            return $next($request);
        }

        // Check if user has a scoped role
        if (!$user->hasAnyRole(['regional-admin', 'club-admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Scoped admins must have their scope assigned. Without it the controllers
        // would fall through their scoping branches and expose data outside the
        // user's organization, so deny access instead.
        if (
            ($user->hasRole('regional-admin') && ! $user->region_id)
            || ($user->hasRole('club-admin') && ! $user->club_id)
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $regionId = $user->region_id;
        $clubId = $user->club_id;

        foreach ($request->route()->parameters() as $param) {
            if (!$param) {
                continue;
            }

            // Route-bound Region model
            if ($param instanceof Region) {
                if ($user->hasRole('regional-admin')) {
                    if ((int) $param->id !== (int) $regionId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                } else {
                    // club-admin cannot access region routes
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
                continue;
            }

            // Route-bound Club model
            if ($param instanceof Club) {
                if ($user->hasRole('regional-admin')) {
                    // regional-admin can access clubs within their region
                    if ((int) $param->region_id !== (int) $regionId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                } elseif ($user->hasRole('club-admin')) {
                    if ((int) $param->id !== (int) $clubId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                }
                continue;
            }

            // Route-bound Member model
            if ($param instanceof Member) {
                if ($user->hasRole('regional-admin')) {
                    // Check member's club is in the user's region
                    $memberRegionId = $param->relationLoaded('club')
                        ? $param->club->region_id
                        : $param->club()->value('region_id');

                    if ((int) $memberRegionId !== (int) $regionId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                } elseif ($user->hasRole('club-admin')) {
                    if ((int) $param->club_id !== (int) $clubId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                }
                continue;
            }

            // Route-bound Certificate model
            if ($param instanceof Certificate) {
                $member = $param->member;

                if (!$member) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }

                if ($user->hasRole('regional-admin')) {
                    $memberRegionId = $member->relationLoaded('club')
                        ? $member->club->region_id
                        : $member->club()->value('region_id');

                    if ((int) $memberRegionId !== (int) $regionId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                } elseif ($user->hasRole('club-admin')) {
                    if ((int) $member->club_id !== (int) $clubId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                }
                continue;
            }

            // Route-bound Payment model
            if ($param instanceof Payment) {
                $member = $param->member;

                if (!$member) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }

                if ($user->hasRole('regional-admin')) {
                    $memberRegionId = $member->relationLoaded('club')
                        ? $member->club->region_id
                        : $member->club()->value('region_id');

                    if ((int) $memberRegionId !== (int) $regionId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                } elseif ($user->hasRole('club-admin')) {
                    if ((int) $member->club_id !== (int) $clubId) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                }
                continue;
            }
        }

        return $next($request);
    }
}
