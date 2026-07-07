<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/member-profile/{slug}', [\App\Http\Controllers\MemberProfileController::class, 'show'])
    ->name('member.profile');

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
