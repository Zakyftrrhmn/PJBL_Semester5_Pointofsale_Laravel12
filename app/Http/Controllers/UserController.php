<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Memastikan user yang login punya permission yang tepat
     */
    public function __construct()
    {
        $this->middleware('permission:user.index')->only('index');
        $this->middleware('permission:user.create')->only(['create', 'store']);
        $this->middleware('permission:user.edit')->only(['edit', 'update']);
        $this->middleware('permission:user.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource (Daftar User).
     */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource (Form Tambah User).
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('pages.user.create', compact('roles'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'roles'     => 'required|string|exists:roles,name',
            'photo_user' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_user')) {
            $photoPath = $request->file('photo_user')->store('users', 'public');
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'photo_user' => $photoPath,
        ]);

        // Perubahan: assignRole dengan single string
        $user->assignRole($request->input('roles')); // Tidak perlu array

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan!');
    }


    /**
     * Show the form for editing the specified resource (Form Edit User).
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all(); // Peran yang dimiliki user

        return view('pages.user.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage (Update User).
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'photo_user' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        if (auth()->user()->hasRole('Super Admin')) {
            if (!$user->hasRole('Super Admin')) {
                $rules['roles'] = 'required|string|exists:roles,name';
            }
        }

        $request->validate($rules);

        // Data dasar
        $data = $request->only('name', 'email');

        // Password baru (jika diisi)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // ✅ PERBAIKAN DI SINI — logika update foto
        if ($request->hasFile('photo_user')) {
            // Hapus foto lama jika ada
            if ($user->photo_user && Storage::disk('public')->exists($user->photo_user)) {
                Storage::disk('public')->delete($user->photo_user);
            }

            // Simpan foto baru ke folder storage/app/public/users
            $data['photo_user'] = $request->file('photo_user')->store('users', 'public');
        }

        // Update user
        $user->update($data);

        // Update role jika Super Admin mengedit user lain
        if (auth()->user()->hasRole('Super Admin') && !$user->hasRole('Super Admin')) {
            $user->syncRoles($request->input('roles'));
        }

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui!');
    }


    /**
     * Remove the specified resource from storage (Hapus User).
     */
    public function destroy(User $user)
    {
        // Cek: Super Admin tidak bisa dihapus
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Akun Super Admin tidak dapat dihapus.');
        }

        // Cek: User tidak dapat menghapus akunnya sendiri
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // ... (Logika hapus foto)
        if ($user->photo_user && Storage::disk('public')->exists($user->photo_user)) {
            Storage::disk('public')->delete($user->photo_user);
        }

        $user->delete();
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus!');
    }
}
