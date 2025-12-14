@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Ubah Password</h1>
        <p class="text-gray-500 mt-1">Ubah password akun Anda</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.change') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                <input type="password" name="current_password" id="current_password" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                <input type="password" name="new_password" id="new_password" required minlength="4"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1">Minimal 4 karakter</p>
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary flex-1">Batal</a>
                <button type="submit" class="btn btn-primary flex-1">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
