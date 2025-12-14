@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
            <p class="text-gray-500 mt-1">Kelola akun pengguna aplikasi</p>
        </div>
    </div>

    <!-- Add New User -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah User Baru</h2>
        <form action="{{ route('users.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div class="w-40">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" name="username" id="username" required placeholder="contoh: john"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 @error('username') border-red-500 @enderror">
                @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" name="name" id="name" required placeholder="Nama Lengkap"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-40">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="text" name="password" id="password" required placeholder="Password"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_admin" value="1" class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                    <span class="text-sm text-gray-700">Admin</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah User
            </button>
        </form>
    </div>

    <!-- User List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Daftar User ({{ $users->count() }})</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reset Password</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 {{ $user->id === auth()->id() ? 'bg-indigo-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $user->is_admin ? 'bg-purple-500' : 'bg-gray-400' }} rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-500">@{{ $user->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_admin)
                                    <span class="badge bg-purple-500 text-white">Admin</span>
                                @else
                                    <span class="badge bg-gray-400 text-white">User</span>
                                @endif
                                
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.toggle-admin', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:underline ml-2">
                                            {{ $user->is_admin ? 'Jadikan User' : 'Jadikan Admin' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.reset-password', $user) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="text" name="new_password" required placeholder="Password baru" 
                                               class="w-32 px-2 py-1 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-indigo-500">
                                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800">Reset</button>
                                    </form>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                          onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">(Anda)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
