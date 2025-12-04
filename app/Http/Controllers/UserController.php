<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        
        return view('users.index', compact('users'));
    }

    public function create()
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $roles = Role::pluck('name');
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'role' => 'required|string|exists:roles,name',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            $user = User::create(collect($validated)->except('role')->toArray());
            $user->assignRole($validated['role']);

            toast('User berhasil ditambahkan.', 'success');
            return redirect()->route('users.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat menyimpan user.');
            return back()->withInput();
        }
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name');
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'role' => 'required|string|exists:roles,name',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        try {
            $user->update(collect($validated)->except('role')->toArray());
            $user->syncRoles([$validated['role']]);

            toast('User berhasil diperbarui.', 'success');
            return redirect()->route('users.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui user.');
            return back()->withInput();
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            toast('User berhasil dihapus.', 'success');
            return redirect()->route('users.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat menghapus user.');
            return back();
        }
    }
}
