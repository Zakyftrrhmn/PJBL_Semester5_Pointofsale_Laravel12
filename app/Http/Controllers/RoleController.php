<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role.index')->only('index');
        $this->middleware('permission:role.create')->only(['create', 'store']);
        $this->middleware('permission:role.edit')->only(['edit', 'update']);
        $this->middleware('permission:role.destroy')->only('destroy');
    }

    /**
     * Menampilkan daftar Role.
     */
    public function index(Request $request)
    {
        $roles = Role::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->where('name', '!=', 'Super Admin') // Sembunyikan Role Super Admin dari daftar
            ->latest()->paginate(15)->withQueryString();

        return view('pages.role.index', compact('roles'));
    }

    /**
     * Menampilkan form tambah Role.
     */
    public function create()
    {
        return view('pages.role.create');
    }

    /**
     * Menyimpan Role baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('role.index')->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit Role (dan atur permission).
     */
    public function edit(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('role.index')->with('error', 'Role Super Admin tidak dapat diubah.');
        }

        // Ambil semua permissions, dikelompokkan berdasarkan 'group'
        $permissions = Permission::all()->groupBy('group');

        // Ambil permissions yang sudah dimiliki role ini
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Memperbarui nama Role.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('role.index')->with('error', 'Nama Role Super Admin tidak dapat diubah.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
        ]);

        $role->update(['name' => $request->name]);

        return redirect()->route('role.edit', $role)->with('success', 'Nama Role berhasil diperbarui!');
    }

    /**
     * Memperbarui Permissions untuk Role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('role.index')->with('error', 'Permissions Role Super Admin tidak dapat diubah.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Sync permissions
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('role.edit', $role)->with('success', 'Hak Akses Role berhasil diperbarui!');
    }


    /**
     * Menghapus Role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('role.index')->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('role.index')->with('success', 'Role berhasil dihapus!');
    }
}
