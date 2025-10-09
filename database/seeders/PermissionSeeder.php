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

            ['name' => 'dashboard.index', 'group' => 'Dashboard Management', 'description' => 'Melihat Dashboard'],


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

            // PEOPLES - Pemasok
            ['name' => 'pemasok.index', 'group' => 'Pemasok', 'description' => 'Melihat daftar pemasok'],
            ['name' => 'pemasok.create', 'group' => 'Pemasok', 'description' => 'Menambah pemasok baru'],
            ['name' => 'pemasok.edit', 'group' => 'Pemasok', 'description' => 'Mengubah data pemasok'],
            ['name' => 'pemasok.destroy', 'group' => 'Pemasok', 'description' => 'Menghapus pemasok'],
            ['name' => 'pemasok.export', 'group' => 'Pemasok', 'description' => 'Export data pemasok'],

            // PEOPLES - Pelanggan
            ['name' => 'pelanggan.index', 'group' => 'Pelanggan', 'description' => 'Melihat daftar pelanggan'],
            ['name' => 'pelanggan.create', 'group' => 'Pelanggan', 'description' => 'Menambah pelanggan baru'],
            ['name' => 'pelanggan.edit', 'group' => 'Pelanggan', 'description' => 'Mengubah data pelanggan'],
            ['name' => 'pelanggan.destroy', 'group' => 'Pelanggan', 'description' => 'Menghapus pelanggan'],
            ['name' => 'pelanggan.export', 'group' => 'Pelanggan', 'description' => 'Export data pelanggan'],

            // INVENTORY - Produk
            ['name' => 'produk.index', 'group' => 'Produk', 'description' => 'Melihat daftar produk'],
            ['name' => 'produk.create', 'group' => 'Produk', 'description' => 'Menambah produk baru'],
            ['name' => 'produk.edit', 'group' => 'Produk', 'description' => 'Mengubah data produk'],
            ['name' => 'produk.destroy', 'group' => 'Produk', 'description' => 'Menghapus produk'],
            ['name' => 'produk.show', 'group' => 'Produk', 'description' => 'Melihat detail produk'],
            ['name' => 'produk.export', 'group' => 'Produk', 'description' => 'Export data produk'],

            // INVENTORY - Kategori
            ['name' => 'kategori.index', 'group' => 'Kategori', 'description' => 'Melihat daftar kategori'],
            ['name' => 'kategori.create', 'group' => 'Kategori', 'description' => 'Menambah kategori baru'],
            ['name' => 'kategori.edit', 'group' => 'Kategori', 'description' => 'Mengubah data kategori'],
            ['name' => 'kategori.destroy', 'group' => 'Kategori', 'description' => 'Menghapus kategori'],

            // INVENTORY - Satuan
            ['name' => 'satuan.index', 'group' => 'Satuan', 'description' => 'Melihat daftar satuan'],
            ['name' => 'satuan.create', 'group' => 'Satuan', 'description' => 'Menambah satuan baru'],
            ['name' => 'satuan.edit', 'group' => 'Satuan', 'description' => 'Mengubah data satuan'],
            ['name' => 'satuan.destroy', 'group' => 'Satuan', 'description' => 'Menghapus satuan'],

            // INVENTORY - Merek
            ['name' => 'merek.index', 'group' => 'Merek', 'description' => 'Melihat daftar merek'],
            ['name' => 'merek.create', 'group' => 'Merek', 'description' => 'Menambah merek baru'],
            ['name' => 'merek.edit', 'group' => 'Merek', 'description' => 'Mengubah data merek'],
            ['name' => 'merek.destroy', 'group' => 'Merek', 'description' => 'Menghapus merek'],

            // ====================================================================================
            // PENAMBAHAN CORE PERMISSION
            // ====================================================================================

            // POINT OF SALE (POS) - Tambahan yang hilang
            ['name' => 'penjualan.pos', 'group' => 'Point of Sale', 'description' => 'Akses dan lakukan transaksi POS (Kasir)'],

            // INVENTORY - Riwayat Penjualan (Invoice)
            ['name' => 'invoice.index', 'group' => 'Penjualan', 'description' => 'Melihat riwayat penjualan (invoice)'],
            ['name' => 'invoice.show', 'group' => 'Penjualan', 'description' => 'Melihat detail invoice'],
            ['name' => 'invoice.export', 'group' => 'Penjualan', 'description' => 'Export data penjualan/cetak struk'],

            // PEMBELIAN
            ['name' => 'pembelian.create', 'group' => 'Pembelian', 'description' => 'Membuat transaksi pembelian baru'],
            ['name' => 'pembelian.index', 'group' => 'Pembelian', 'description' => 'Melihat daftar transaksi pembelian'],
            ['name' => 'pembelian.destroy', 'group' => 'Pembelian', 'description' => 'Menghapus transaksi pembelian'],

            // PERBAIKAN DAN PENAMBAHAN UNTUK PEMBELIAN DAN PESANAN PEMBELIAN

            // PEMBELIAN (Untuk transaksi baru/CRUD utama)
            ['name' => 'pembelian.create', 'group' => 'Pembelian', 'description' => 'Membuat transaksi pembelian baru'],
            ['name' => 'pembelian.index', 'group' => 'Pembelian', 'description' => 'Melihat daftar transaksi pembelian'],
            ['name' => 'pembelian.show', 'group' => 'Pembelian', 'description' => 'Melihat detail transaksi pembelian'], // *TAMBAHAN*
            ['name' => 'pembelian.edit', 'group' => 'Pembelian', 'description' => 'Mengubah transaksi pembelian'], // *TAMBAHAN*
            ['name' => 'pembelian.destroy', 'group' => 'Pembelian', 'description' => 'Menghapus transaksi pembelian'],
            ['name' => 'pembelian.export', 'group' => 'Pembelian', 'description' => 'Mencetak/export faktur pembelian'], // *TAMBAHAN*

            // Karena Anda membedakan Controller, kita pertahankan permission Pesanan-Pembelian/History
            // Gunakan group 'Pembelian' saja agar lebih rapih di UI Role/Permission
            ['name' => 'pesanan-pembelian.index', 'group' => 'Pembelian', 'description' => 'Melihat riwayat pesanan pembelian'],
            ['name' => 'pesanan-pembelian.show', 'group' => 'Pembelian', 'description' => 'Melihat detail pesanan pembelian'], // *TAMBAHAN*
            ['name' => 'pesanan-pembelian.export', 'group' => 'Pembelian', 'description' => 'Mencetak faktur pesanan pembelian'], // *TAMBAHAN*

            // Retur Pembelian
            ['name' => 'retur-pembelian.index', 'group' => 'Retur Pembelian', 'description' => 'Melihat daftar retur pembelian'],
            ['name' => 'retur-pembelian.create', 'group' => 'Retur Pembelian', 'description' => 'Membuat retur pembelian baru'],
            ['name' => 'retur-pembelian.edit', 'group' => 'Retur Pembelian', 'description' => 'Mengubah data retur pembelian'],
            ['name' => 'retur-pembelian.destroy', 'group' => 'Retur Pembelian', 'description' => 'Menghapus retur pembelian'],


            // Retur Pembelian
            ['name' => 'retur-penjualan.index', 'group' => 'Retur penjualan', 'description' => 'Melihat daftar retur penjualan'],
            ['name' => 'retur-penjualan.create', 'group' => 'Retur penjualan', 'description' => 'Membuat retur penjualan baru'],
            ['name' => 'retur-penjualan.edit', 'group' => 'Retur penjualan', 'description' => 'Mengubah data retur penjualan'],
            ['name' => 'retur-penjualan.destroy', 'group' => 'Retur penjualan', 'description' => 'Menghapus retur penjualan'],

            // Barcode
            ['name' => 'barcode.index', 'group' => 'Barcode', 'description' => 'Akses halaman cetak barcode'],

            // System Management
            ['name' => 'backup.index', 'group' => 'System Management', 'description' => 'Menjalankan backup database'],
            ['name' => 'backup.create', 'group' => 'System Management', 'description' => 'Menjalankan backup database'],
            ['name' => 'backup.delete', 'group' => 'System Management', 'description' => 'Menjalankan backup database'],


        ];

        // Masukkan semua permission ke database
        foreach ($permissions as $item) {
            Permission::firstOrCreate($item);
        }

        // 2. Definisikan Roles
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleKasir      = Role::firstOrCreate(['name' => 'Kasir']);
        $roleStafGudang = Role::firstOrCreate(['name' => 'Staf Gudang']);

        // 3. Assign Permissions ke Roles
        // Super Admin: Semua Permission
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // Kasir: Hanya fitur yang relevan untuk transaksi penjualan dan data customer
        $kasirPermissions = [
            'pelanggan.index',
            'pelanggan.create',
            'pelanggan.edit',
            'pemasok.index',

            // Akses ke form dan proses POS (WAJIB ADA)
            'penjualan.pos',

            // Akses ke riwayat penjualan
            'invoice.index',
        ];
        $roleKasir->syncPermissions($kasirPermissions);

        // Staf Gudang: Fokus pada Inventory dan Pembelian/Retur
        $gudangPermissions = [
            'produk.index',
            'produk.create',
            'produk.edit',
            'produk.destroy',
            'produk.export',
            'produk.show',
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

            // Pembelian
            'pembelian.create',
            'pembelian.index', // Tambahkan index agar bisa lihat riwayat pembelian
            'pesanan-pembelian.index',
            'retur-pembelian.index',

            // Barcode
            'barcode.index',
        ];
        $roleStafGudang->syncPermissions($gudangPermissions);

        // 4. Assign Role ke User Pertama (Contoh)
        $user = User::first();
        if ($user) {
            $user->assignRole($roleSuperAdmin);
        }
    }
}
