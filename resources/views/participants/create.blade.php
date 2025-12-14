@extends('layouts.app')

@section('title', 'Tambah Peserta')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('participants.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Daftar Peserta
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Tambah Peserta Baru</h1>
        <p class="text-gray-500 mt-1">Isi data peserta bis Natal</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <form action="{{ route('participants.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Anak <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 mb-2">Usia</label>
                    <input type="number" name="age" id="age" value="{{ old('age') }}" min="1" max="100"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor HP</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label for="guardian_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Orang Tua/Pendamping</label>
                <input type="text" name="guardian_name" id="guardian_name" value="{{ old('guardian_name') }}"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                <select name="category" id="category" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="perform" {{ old('category') === 'perform' ? 'selected' : '' }}>PERFORM (Berangkat 06:00)</option>
                    <option value="umum" {{ old('category', 'umum') === 'umum' ? 'selected' : '' }}>UMUM (Berangkat 07:00)</option>
                    <option value="pribadi" {{ old('category') === 'pribadi' ? 'selected' : '' }}>PRIBADI (Tidak Ikut Bis)</option>
                </select>
            </div>

            <div class="bg-indigo-50 rounded-lg p-4">
                <h4 class="font-medium text-indigo-800 mb-2">Keterangan Kategori:</h4>
                <ul class="text-sm text-indigo-700 space-y-1">
                    <li>• <strong>PERFORM</strong>: Berangkat jam 6 pagi, bisa dipisah dari pendamping</li>
                    <li>• <strong>UMUM</strong>: Berangkat jam 7 pagi, HARUS bersama pendamping</li>
                    <li>• <strong>PRIBADI</strong>: Tidak terdaftar sebagai peserta bis</li>
                </ul>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('participants.index') }}" class="btn btn-secondary flex-1">Batal</a>
                <button type="submit" class="btn btn-primary flex-1">Simpan Peserta</button>
            </div>
        </form>
    </div>
</div>
@endsection
