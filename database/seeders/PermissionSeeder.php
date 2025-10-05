<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User; // Pastikan Anda mengimpor Model User

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Permissions
        $permissions = [
            // User Management
            ['name' => 'user.index', 'group' => 'User Management', 'description' => 'Melihat daftar user'],
            ['name' => 'user.create', 'group' => 'User Management', 'description' => 'Menambah user baru'],
            ['name' => 'user.edit', 'group' => 'User Management', 'description' => 'Mengubah data user'],
            ['name' => 'user.destroy', 'group' => 'User Management', 'description' => 'Menghapus user'],

            // Role & Permission Management
            ['name' => 'role.index', 'group' => 'Role & Permission', 'description' => 'Melihat daftar role'],
            ['name' => 'role.create', 'group' => 'Role & Permission', 'description' => 'Menambah role baru'],
            ['name' => 'role.edit', 'group' => 'Role & Permission', 'description' => 'Mengubah role dan hak akses'],
            ['name' => 'role.destroy', 'group' => 'Role & Permission', 'description' => 'Menghapus role'],

            // Contoh Modul Lain (Pemasok)
            ['name' => 'pemasok.index', 'group' => 'Pemasok', 'description' => 'Melihat daftar pemasok'],
            ['name' => 'pemasok.create', 'group' => 'Pemasok', 'description' => 'Menambah pemasok baru'],
            ['name' => 'pemasok.edit', 'group' => 'Pemasok', 'description' => 'Mengubah data pemasok'],
            ['name' => 'pemasok.destroy', 'group' => 'Pemasok', 'description' => 'Menghapus pemasok'],
            ['name' => 'pemasok.export', 'group' => 'Pemasok', 'description' => 'Export data pemasok'], // Tambahan

            // Produk
            ['name' => 'produk.index', 'group' => 'Produk', 'description' => 'Melihat daftar produk'],
            ['name' => 'produk.create', 'group' => 'Produk', 'description' => 'Menambah produk baru'],
            ['name' => 'produk.edit', 'group' => 'Produk', 'description' => 'Mengubah data produk'],
            ['name' => 'produk.destroy', 'group' => 'Produk', 'description' => 'Menghapus produk'],
            ['name' => 'produk.show', 'group' => 'Produk', 'description' => 'Melihat detail produk'],
            ['name' => 'produk.export', 'group' => 'Produk', 'description' => 'Export data produk'],

            // Kategori
            ['name' => 'kategori.index', 'group' => 'Kategori', 'description' => 'Melihat daftar kategori'],
            ['name' => 'kategori.create', 'group' => 'Kategori', 'description' => 'Menambah kategori baru'],
            ['name' => 'kategori.edit', 'group' => 'Kategori', 'description' => 'Mengubah data kategori'],
            ['name' => 'kategori.destroy', 'group' => 'Kategori', 'description' => 'Menghapus kategori'],

            // Pelanggan
            ['name' => 'pelanggan.index', 'group' => 'Pelanggan', 'description' => 'Melihat daftar pelanggan'],
            ['name' => 'pelanggan.create', 'group' => 'Pelanggan', 'description' => 'Menambah pelanggan baru'],
            ['name' => 'pelanggan.edit', 'group' => 'Pelanggan', 'description' => 'Mengubah data pelanggan'],
            ['name' => 'pelanggan.destroy', 'group' => 'Pelanggan', 'description' => 'Menghapus pelanggan'],
            ['name' => 'pelanggan.export', 'group' => 'Pelanggan', 'description' => 'Export data pelanggan'],

            // Satuan
            ['name' => 'satuan.index', 'group' => 'Satuan', 'description' => 'Melihat daftar satuan'],
            ['name' => 'satuan.create', 'group' => 'Satuan', 'description' => 'Menambah satuan baru'],
            ['name' => 'satuan.edit', 'group' => 'Satuan', 'description' => 'Mengubah data satuan'],
            ['name' => 'satuan.destroy', 'group' => 'Satuan', 'description' => 'Menghapus satuan'],

            // Merek
            ['name' => 'merek.index', 'group' => 'Merek', 'description' => 'Melihat daftar merek'],
            ['name' => 'merek.create', 'group' => 'Merek', 'description' => 'Menambah merek baru'],
            ['name' => 'merek.edit', 'group' => 'Merek', 'description' => 'Mengubah data merek'],
            ['name' => 'merek.destroy', 'group' => 'Merek', 'description' => 'Menghapus merek'],

        ];

        foreach ($permissions as $item) {
            Permission::firstOrCreate($item);
        }

        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleKasir      = Role::firstOrCreate(['name' => 'Kasir']);
        $roleStafGudang = Role::firstOrCreate(['name' => 'Staf Gudang']);

        $roleSuperAdmin->givePermissionTo(Permission::all());

        $kasirPermissions = [
            'pemasok.index',
            'pelanggan.index',
            'pelanggan.create',
            'pelanggan.edit',
        ];
        $roleKasir->syncPermissions($kasirPermissions);

        $gudangPermissions = [
            'produk.index',
            'produk.create',
            'produk.edit',
            'produk.destroy',
            'produk.export',
            'kategori.index',
            'kategori.create',
            'kategori.edit',
            'kategori.destroy',
            'satuan.index',
            'satuan.create',
            'satuan.edit',
            'satuan.destroy',
            'merek.index',
            'merek.create',
            'merek.edit',
            'merek.destroy',
        ];
        $roleStafGudang->syncPermissions($gudangPermissions);
        $user = User::first();
        if ($user) {
            $user->assignRole($roleSuperAdmin);
        }
    }
}
