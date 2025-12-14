<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display user list (Admin only)
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Store new user (Admin only)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'name' => 'required|string|max:100',
            'password' => 'required|string|min:4',
            'is_admin' => 'boolean',
        ]);

        $user = User::create([
            'username' => strtolower($validated['username']),
            'name' => $validated['name'],
            'email' => strtolower($validated['username']) . '@bissm.local',
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        ActivityLog::log('create', "Menambahkan user: {$user->name} (@{$user->username})", $user);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan!");
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $name = $user->name;
        $username = $user->username;
        $user->delete();

        ActivityLog::log('delete', "Menghapus user: {$name} (@{$username})");

        return redirect()->route('users.index')
            ->with('success', "User {$name} berhasil dihapus!");
    }

    /**
     * Reset password (Admin only)
     */
    public function resetPassword(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:4',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        ActivityLog::log('update', "Reset password untuk user: {$user->name} (@{$user->username})", $user);

        return redirect()->route('users.index')
            ->with('success', "Password {$user->name} berhasil direset!");
    }

    /**
     * Show change password form (All users)
     */
    public function showChangePassword()
    {
        return view('users.change-password');
    }

    /**
     * Change own password (All users)
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:4|confirmed',
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah!']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        ActivityLog::log('update', "Mengubah password sendiri", $user);

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diubah!');
    }

    /**
     * Toggle admin status (Admin only)
     */
    public function toggleAdmin(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        // Prevent removing admin from yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat mengubah status admin sendiri!');
        }

        $user->update([
            'is_admin' => !$user->is_admin,
        ]);

        $status = $user->is_admin ? 'Admin' : 'User biasa';
        ActivityLog::log('update', "Mengubah status {$user->name} menjadi {$status}", $user);

        return redirect()->route('users.index')
            ->with('success', "Status {$user->name} diubah menjadi {$status}!");
    }
}
