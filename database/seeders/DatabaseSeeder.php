<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@subtitles-finder.com', // Password: password
        ]);

        $role = Role::findOrCreate('admin');
        $user = User::where('email', 'admin@subtitles-finder.com')->first();
        $user->assignRole($role);

        $roles = [
            'video manager' => [
                'admin.video.index',
                'admin.video.create',
                'admin.video.show',
                'admin.video.edit',
                'admin.video.delete',
            ],
            'playlist manager' => [
                'admin.playlist.index',
                'admin.playlist.create',
                'admin.playlist.show',
                'admin.playlist.edit',
                'admin.playlist.delete',
            ],
            'fragment manager' => [
                'admin.fragment.index',
                'admin.fragment.create',
                'admin.fragment.show',
                'admin.fragment.edit',
                'admin.fragment.delete',
            ],
            'user manager' => [
                'admin.user.index',
                'admin.user.create',
                'admin.user.show',
                'admin.user.edit',
                'admin.user.delete',
            ],
            'role manager' => [
                'admin.role.index',
                'admin.role.create',
                'admin.role.show',
                'admin.role.edit',
                'admin.role.delete',
            ],
            'search manager' => [
                'admin.search.index',
            ],
        ];

        $basePermission = Permission::findOrCreate('admin.panel.access');
        foreach ($roles as $name => $permissionNames) {
            $role = Role::findOrCreate($name);
            $permissions = [];
            foreach ($permissionNames as $permissionName) {
                $permissions[] = Permission::findOrCreate($permissionName);
            }
            $permissions[] = $basePermission;
            $role->syncPermissions($permissions);
        }
    }
}
