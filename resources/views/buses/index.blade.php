@extends('layouts.app')

@section('title', 'Manajemen Bis')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Bis</h1>
            <p class="text-gray-500 mt-1">Kelola bis untuk keberangkatan Natal</p>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <!-- Default Capacity Setting -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pengaturan Kapasitas Default</h2>
        <form action="{{ route('buses.default-capacity') }}" method="POST" class="flex gap-4 items-end">
            @csrf
            <div class="flex-1 max-w-xs">
                <label for="default_capacity" class="block text-sm font-medium text-gray-700 mb-2">Kapasitas Default per Bis</label>
                <input type="number" name="default_capacity" id="default_capacity" value="{{ $defaultCapacity }}" 
                       min="1" max="100" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Add New Bus -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah Bis Baru</h2>
        <form action="{{ route('buses.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Bis</label>
                <input type="text" name="name" id="name" placeholder="Bis 1" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-32">
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Kapasitas</label>
                <input type="number" name="capacity" id="capacity" value="{{ $defaultCapacity }}" min="1" max="100" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-48">
                <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-2">Jam Berangkat</label>
                <select name="departure_time" id="departure_time" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="06:00">06:00 (PERFORM JUNIOR)</option>
                    <option value="07:00" selected>07:00 (UMUM)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Bis
            </button>
        </form>
    </div>
    @endif

    <!-- Bus List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($buses as $bus)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 {{ $bus->departure_time === '06:00' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }} rounded-xl flex items-center justify-center font-bold text-lg">
                                {{ preg_replace('/[^0-9]/', '', $bus->name) ?: '?' }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $bus->name }}</h3>
                                <p class="text-sm {{ $bus->departure_time === '06:00' ? 'text-emerald-600' : 'text-blue-600' }}">
                                    {{ $bus->departure_time_label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Progress -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-500">Terisi</span>
                            <span class="font-semibold {{ $bus->isFull() ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $bus->occupied_seats }} / {{ $bus->capacity }}
                            </span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $bus->status_class }} rounded-full transition-all duration-300" 
                                 style="width: {{ min($bus->occupancy_percentage, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $bus->remaining_capacity }} kursi tersisa</p>
                    </div>

                    @if(auth()->user()->isAdmin())
                    <!-- Edit Form -->
                    <form action="{{ route('buses.update', $bus) }}" method="POST" class="space-y-3 mb-4">
                        @csrf
                        @method('PUT')
                        <div class="flex gap-2">
                            <input type="text" name="name" value="{{ $bus->name }}" required
                                   class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <input type="number" name="capacity" value="{{ $bus->capacity }}" min="1" max="100" required
                                   class="w-20 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <select name="departure_time" required
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="06:00" {{ $bus->departure_time === '06:00' ? 'selected' : '' }}>06:00 (PERFORM)</option>
                            <option value="07:00" {{ $bus->departure_time === '07:00' ? 'selected' : '' }}>07:00 (UMUM)</option>
                        </select>
                        <button type="submit" class="w-full btn btn-secondary text-sm">Simpan Perubahan</button>
                    </form>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('buses.show', $bus) }}" class="flex-1 btn btn-primary text-sm">
                            Lihat Penumpang
                        </a>
                        @if(auth()->user()->isAdmin() && $bus->occupied_seats === 0)
                            <form action="{{ route('buses.destroy', $bus) }}" method="POST" 
                                  onsubmit="return confirm('Hapus {{ $bus->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger text-sm px-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl shadow-sm p-12 border border-gray-100 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Bis</h3>
                <p class="text-gray-500">Tambahkan bis baru menggunakan form di atas</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
