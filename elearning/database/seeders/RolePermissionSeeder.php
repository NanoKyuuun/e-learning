<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Admin System Permissions
            'access_admin_panel',
            'manage_users',
            'manage_roles',
            'manage_academic_years',
            'manage_semesters',
            'manage_departments',
            'view_system_logs',

            // Kajur Permissions
            'access_kajur_panel',
            'manage_subjects',
            'manage_class_groups',
            'manage_teaching_assignments',
            'manage_schedules',
            'view_academic_monitoring',

            // Guru Permissions
            'access_guru_panel',
            'manage_meetings',
            'manage_materials',
            'manage_assignments',
            'grade_submissions',

            // Siswa Permissions
            'access_siswa_panel',
            'view_materials',
            'submit_assignments',
            'view_grades',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin System
        $adminRole = Role::firstOrCreate(['name' => 'admin-sistem']);
        $adminRole->givePermissionTo(Permission::all());

        // Kajur
        $kajurRole = Role::firstOrCreate(['name' => 'kajur']);
        $kajurRole->givePermissionTo([
            'access_kajur_panel',
            'manage_subjects',
            'manage_class_groups',
            'manage_teaching_assignments',
            'manage_schedules',
            'view_academic_monitoring',
        ]);

        // Guru
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $guruRole->givePermissionTo([
            'access_guru_panel',
            'manage_meetings',
            'manage_materials',
            'manage_assignments',
            'grade_submissions',
        ]);

        // Siswa
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);
        $siswaRole->givePermissionTo([
            'access_siswa_panel',
            'view_materials',
            'submit_assignments',
            'view_grades',
        ]);
    }
}
