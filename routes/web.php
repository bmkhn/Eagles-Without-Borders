<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/member-directory', function () {
    $q = request()->string('q')->trim()->toString();

    $regions = \App\Models\Region::query()
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

    // Filter out clubs with no members (when searching)
    if ($q !== '') {
        $regions = $regions->filter(function ($region) {
            $region->clubs = $region->clubs->filter(function ($club) {
                return $club->members->count() > 0;
            });
            return $region->clubs->count() > 0;
        })->values();
    }

    return view('public.member-directory', [
        'regions' => $regions,
        'q' => $q,
    ]);
})->name('member.directory');

Route::get('/member-profile/{slug}', function (string $slug) {
    return view('public.member-profile', ['slug' => $slug]);
})->name('member.profile');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin/login', function () {
    return redirect()->route('login');
})->name('admin.login');

Route::middleware(['auth', 'club.scope'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('regions', \App\Http\Controllers\Admin\RegionController::class)
        ->middleware('role:national-president')
        ->names([
            'index' => 'admin.regions.index',
            'create' => 'admin.regions.create',
            'store' => 'admin.regions.store',
            'edit' => 'admin.regions.edit',
            'update' => 'admin.regions.update',
            'destroy' => 'admin.regions.destroy',
        ]);

    Route::resource('clubs', \App\Http\Controllers\Admin\ClubController::class)
        ->middleware('role:national-president')
        ->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ])->names([
            'index' => 'admin.clubs.index',
            'create' => 'admin.clubs.create',
            'store' => 'admin.clubs.store',
            'edit' => 'admin.clubs.edit',
            'update' => 'admin.clubs.update',
            'destroy' => 'admin.clubs.destroy',
        ]);

    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)
        ->middleware('role:national-president')
        ->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ])->names([
            'index' => 'admin.positions.index',
            'create' => 'admin.positions.create',
            'store' => 'admin.positions.store',
            'edit' => 'admin.positions.edit',
            'update' => 'admin.positions.update',
            'destroy' => 'admin.positions.destroy',
        ]);

    Route::get('/member-directory', [\App\Http\Controllers\Admin\MemberController::class, 'directory'])
        ->middleware('role:national-president|club-president')
        ->name('admin.members.directory');

    Route::resource('members', \App\Http\Controllers\Admin\MemberController::class)
        ->middleware('role:national-president|club-president')
        ->except(['show'])
        ->names([
            'index' => 'admin.members.index',
            'create' => 'admin.members.create',
            'store' => 'admin.members.store',
            'edit' => 'admin.members.edit',
            'update' => 'admin.members.update',
            'destroy' => 'admin.members.destroy',
        ]);

    Route::get('/{any}', function () {
        return view('admin.*');
    })->where('any', '.*')->name('admin.catchall');
});

require __DIR__.'/auth.php';
