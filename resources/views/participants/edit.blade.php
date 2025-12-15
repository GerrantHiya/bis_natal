@extends('layouts.app')

@section('title', 'Edit Peserta')

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
        <h1 class="text-3xl font-bold text-gray-900">Edit Peserta</h1>
        <p class="text-gray-500 mt-1">Perbarui data {{ $participant->name }}</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <form action="{{ route('participants.update', $participant) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Anak <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $participant->name) }}" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 mb-2">Usia</label>
                    <input type="number" name="age" id="age" value="{{ old('age', $participant->age) }}" min="1" max="100"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor HP</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $participant->phone) }}" placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label for="guardian_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Orang Tua/Pendamping</label>
                <input type="text" name="guardian_name" id="guardian_name" value="{{ old('guardian_name', $participant->guardian_name) }}"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                <select name="category" id="category" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="perform" {{ old('category', $participant->category) === 'perform' ? 'selected' : '' }}>PERFORM (Berangkat 06:00)</option>
                    <option value="umum" {{ old('category', $participant->category) === 'umum' ? 'selected' : '' }}>UMUM (Berangkat 07:00)</option>
                    <option value="pribadi" {{ old('category', $participant->category) === 'pribadi' ? 'selected' : '' }}>PRIBADI (Tidak Ikut Bis)</option>
                </select>
            </div>

            <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <input type="checkbox" name="is_kiddies_prioritas" id="is_kiddies_prioritas" value="1"
                       {{ old('is_kiddies_prioritas', $participant->is_kiddies_prioritas) ? 'checked' : '' }}
                       class="w-5 h-5 text-amber-600 border-amber-300 rounded focus:ring-amber-500">
                <label for="is_kiddies_prioritas" class="text-sm font-medium text-amber-800">
                    ⭐ Kiddies Prioritas
                    <span class="block text-xs font-normal text-amber-600">Peserta ini akan diprioritaskan dan dikelompokkan bersama di bis terdepan</span>
                </label>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('participants.index') }}" class="btn btn-secondary flex-1">Batal</a>
                <button type="submit" class="btn btn-primary flex-1">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
