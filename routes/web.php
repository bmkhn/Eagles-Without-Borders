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

Route::middleware(['auth', 'scope'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Super Admin only: admin management
    Route::middleware(['role:super-admin', 'permission:manage-admins'])->group(function () {
        Route::get('/admins', [\App\Http\Controllers\Admin\AdminController::class, 'index'])
            ->name('admin.admins.index');
        Route::get('/admins/create', [\App\Http\Controllers\Admin\AdminController::class, 'create'])
            ->name('admin.admins.create');
        Route::post('/admins', [\App\Http\Controllers\Admin\AdminController::class, 'store'])
            ->name('admin.admins.store');
        Route::get('/admins/{admin}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'edit'])
            ->name('admin.admins.edit');
        Route::patch('/admins/{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'update'])
            ->name('admin.admins.update');
        Route::delete('/admins/{admin}', [\App\Http\Controllers\Admin\AdminController::class, 'destroy'])
            ->name('admin.admins.destroy');
    });

    // Super Admin & National Admin: audit logs
    Route::middleware(['role:super-admin|national-admin', 'permission:view-audit-logs'])->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AdminController::class, 'auditLogs'])
            ->name('admin.audit-logs');
    });

    // Regions: Super Admin & National Admin only
    Route::resource('regions', \App\Http\Controllers\Admin\RegionController::class)
        ->middleware(['role:super-admin|national-admin', 'permission:view-regions|create-regions|edit-regions|delete-regions'])
        ->names([
            'index' => 'admin.regions.index',
            'create' => 'admin.regions.create',
            'store' => 'admin.regions.store',
            'edit' => 'admin.regions.edit',
            'update' => 'admin.regions.update',
            'destroy' => 'admin.regions.destroy',
        ]);

    // Clubs: Super Admin, National Admin, & Regional Admin (scoped to their region)
    Route::resource('clubs', \App\Http\Controllers\Admin\ClubController::class)
        ->middleware(['role:super-admin|national-admin|regional-admin', 'permission:view-clubs|create-clubs|edit-clubs|delete-clubs'])
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

    // Positions: Super Admin & National Admin only
    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)
        ->middleware(['role:super-admin|national-admin', 'permission:view-positions|create-positions|edit-positions|delete-positions'])
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

    // Members: All admin roles (scoped)
    Route::resource('members', \App\Http\Controllers\Admin\MemberController::class)
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:view-members|create-members|edit-members|delete-members'])
        ->except(['show'])
        ->names([
            'index' => 'admin.members.index',
            'create' => 'admin.members.create',
            'store' => 'admin.members.store',
            'edit' => 'admin.members.edit',
            'update' => 'admin.members.update',
            'destroy' => 'admin.members.destroy',
        ]);

    // Trashed / Recycle Bin
    Route::get('/members/trashed', [\App\Http\Controllers\Admin\MemberController::class, 'trashed'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:view-members|delete-members'])
        ->name('admin.members.trashed');

    Route::patch('/members/{id}/restore', [\App\Http\Controllers\Admin\MemberController::class, 'restore'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:delete-members'])
        ->name('admin.members.restore');

    Route::delete('/members/{id}/force-delete', [\App\Http\Controllers\Admin\MemberController::class, 'forceDestroy'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:delete-members'])
        ->name('admin.members.force-destroy');

    Route::get('/members/export', [\App\Http\Controllers\Admin\MemberController::class, 'export'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:view-members'])
        ->name('admin.members.export');

    Route::post('/members/import', [\App\Http\Controllers\Admin\MemberController::class, 'import'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:create-members'])
        ->name('admin.members.import');

    // Payments (scoped to member access)
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:view-members'])
        ->name('admin.payments.index');

    Route::get('/payments/create', [\App\Http\Controllers\Admin\PaymentController::class, 'create'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:edit-members'])
        ->name('admin.payments.create');

    Route::post('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:edit-members'])
        ->name('admin.payments.store');

    Route::get('/payments/{payment}/edit', [\App\Http\Controllers\Admin\PaymentController::class, 'edit'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:edit-members'])
        ->name('admin.payments.edit');

    Route::put('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'update'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:edit-members'])
        ->name('admin.payments.update');

    Route::delete('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])
        ->middleware(['role:super-admin|national-admin|regional-admin|club-admin', 'permission:edit-members'])
        ->name('admin.payments.destroy');

    Route::get('/{any}', function () {
        return view('admin.*');
    })->where('any', '.*')->name('admin.catchall');
});

require __DIR__.'/auth.php';
