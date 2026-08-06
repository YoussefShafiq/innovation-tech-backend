<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Nexus Engineering admin dashboard
        $permissions = [
            // Admin Management
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',
            
            // Permission Management
            'view_permissions',
            'assign_permissions',
            
            // Services Management
            'view_services',
            'create_services',
            'edit_services',
            'delete_services',

            // Settings Management
            'view_settings',
            'edit_settings',

            // Contact Management
            'view_contacts',
            'create_contacts',
            'edit_contacts',
            'delete_contacts',

            // Team Management
            'view_team',
            'create_team',
            'edit_team',
            'delete_team',

            // Partners Management
            'view_partners',
            'create_partners',
            'edit_partners',
            'delete_partners',

            // Page content (CMS sections)
            'view_pages',
            'edit_pages',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        // Create super admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@innovation-tech.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('InnovationTech2024!'),
                'profile_image' => null,
            ]
        );

        // Assign admin role to super admin
        $superAdmin->assignRole($adminRole);
        
        // Give super admin ALL permissions directly
        $superAdmin->syncPermissions(Permission::all());

        // Create a demo admin user for testing
        $demoAdmin = User::updateOrCreate(
            ['email' => 'demo@innovation-tech.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('DemoAdmin2024!'),
                'profile_image' => null,
            ]
        );

        // Assign admin role to demo user
        $demoAdmin->assignRole($adminRole);
        
        // Give demo admin limited permissions
        $demoAdmin->syncPermissions([
            'view_admins',
            'view_services',
            'view_settings',
            'view_team',
            'view_contacts',
            'view_permissions',
            'view_partners',
            'view_pages',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin: admin@innovation-tech.com / InnovationTech2024!');
        $this->command->info('Demo Admin: demo@innovation-tech.com / DemoAdmin2024!');
    }
}
