<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles if they don't already exist
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $member = Role::firstOrCreate(['name' => 'member']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);

        // Create Permissions if they don't already exist
        $createTask = Permission::firstOrCreate(['name' => 'create_task']);
        $updateTask = Permission::firstOrCreate(['name' => 'update_task']);
        $deleteTask = Permission::firstOrCreate(['name' => 'delete_task']);
        $viewTask = Permission::firstOrCreate(['name' => 'view_task']);

        // Attach permissions to roles
        $admin->permissions()->syncWithoutDetaching([$createTask->id, $updateTask->id, $deleteTask->id, $viewTask->id]);
        $member->permissions()->syncWithoutDetaching([$createTask->id, $updateTask->id, $viewTask->id]);
        $viewer->permissions()->syncWithoutDetaching([$viewTask->id]);
    }
}
