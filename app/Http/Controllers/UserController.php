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

    /**
     * Store a newly created resource in storage (Simpan User Baru).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'roles'     => 'required|array',
            'roles.*'   => 'exists:roles,name',
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

        // Beri peran (role) kepada user
        $user->assignRole($request->input('roles'));

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
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'roles'     => 'required|array',
            'roles.*'   => 'exists:roles,name',
            'photo_user' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ]);

        $data = $request->only('name', 'email');

        // Update password jika diisi
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo_user')) {
            // hapus foto lama jika ada
            if ($user->photo_user && Storage::disk('public')->exists($user->photo_user)) {
                Storage::disk('public')->delete($user->photo_user);
            }
            $data['photo_user'] = $request->file('photo_user')->store('users', 'public');
        }

        $user->update($data);

        // Sinkronkan peran (role)
        $user->syncRoles($request->input('roles'));

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage (Hapus User).
     */
    public function destroy(User $user)
    {
        if ($user->photo_user && Storage::disk('public')->exists($user->photo_user)) {
            Storage::disk('public')->delete($user->photo_user);
        }

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus!');
    }
}
