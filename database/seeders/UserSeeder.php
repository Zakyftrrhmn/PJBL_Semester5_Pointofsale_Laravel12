<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Role (jika belum dibuat di seeder lain, ini memastikan role 'Super Admin' ada)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        // Role::firstOrCreate(['name' => 'Admin']);
        // Role::firstOrCreate(['name' => 'Kasir']);

        // 2. Buat User Super Admin
        $user = User::firstOrCreate(
            ['email' => 'superadmin@pos.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Password default: 'password'
            ]
        );

        // 3. Berikan Peran (Role) Super Admin ke User
        // Hanya tambahkan peran jika user belum memiliki peran 'Super Admin'
        if (!$user->hasRole('Super Admin')) {
            $user->assignRole($superAdminRole);
        }

        // Catatan: Jika Anda ingin Super Admin memiliki semua izin (Permission),
        // Anda bisa tambahkan logika di sini (misalnya, berikan semua permission
        // yang ada di DB ke role 'Super Admin')
        if ($superAdminRole->name === 'Super Admin') {
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
        }
    }
}
