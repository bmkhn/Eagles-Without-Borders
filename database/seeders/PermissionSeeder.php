<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Define all permissions ───────────────────────────────────
        $permissions = [
            // Regions
            'view-regions',
            'create-regions',
            'edit-regions',
            'delete-regions',

            // Clubs
            'view-clubs',
            'create-clubs',
            'edit-clubs',
            'delete-clubs',

            // Positions
            'view-positions',
            'create-positions',
            'edit-positions',
            'delete-positions',

            // Members
            'view-members',
            'create-members',
            'edit-members',
            'delete-members',

            // Admin management
            'manage-admins',

            // Audit logs
            'view-audit-logs',
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm, 'guard_name' => 'web'],
                []
            );
        }

        // ─── Assign permissions to roles ─────────────────────────────

        // super-admin: EVERYTHING
        Role::findByName('super-admin', 'web')
            ->syncPermissions(Permission::all());

        // national-admin: everything except manage-admins (which is now granted below)
        Role::findByName('national-admin', 'web')
            ->syncPermissions([
                'view-regions',
                'create-regions',
                'edit-regions',
                'delete-regions',
                'view-clubs',
                'create-clubs',
                'edit-clubs',
                'delete-clubs',
                'view-positions',
                'create-positions',
                'edit-positions',
                'delete-positions',
                'view-members',
                'create-members',
                'edit-members',
                'delete-members',
                'manage-admins',
                'view-audit-logs',
            ]);

        // regional-admin: clubs + members + audit logs (scoped by middleware)
        Role::findByName('regional-admin', 'web')
            ->syncPermissions([
                'view-clubs',
                'create-clubs',
                'edit-clubs',
                'delete-clubs',
                'view-members',
                'create-members',
                'edit-members',
                'delete-members',
                'view-audit-logs',
            ]);

        // club-admin: members + audit logs (scoped by middleware)
        Role::findByName('club-admin', 'web')
            ->syncPermissions([
                'view-members',
                'create-members',
                'edit-members',
                'delete-members',
                'view-audit-logs',
            ]);

        $this->command->info('Permissions seeded and assigned to roles.');
    }
}
