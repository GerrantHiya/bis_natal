@extends('layouts.app')

@section('title', $bus->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('buses.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Manajemen Bis
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $bus->name }}</h1>
            <p class="text-gray-500 mt-1">{{ $bus->departure_time_label }}</p>
        </div>
        <div class="text-right">
            <p class="text-3xl font-bold {{ $bus->isFull() ? 'text-red-600' : 'text-gray-900' }}">
                {{ $bus->occupied_seats }} / {{ $bus->capacity }}
            </p>
            <p class="text-sm text-gray-500">Penumpang</p>
        </div>
    </div>

    <!-- Capacity Bar -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Kapasitas Terisi</span>
            <span class="text-sm text-gray-500">{{ $bus->occupancy_percentage }}%</span>
        </div>
        <div class="w-full h-4 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full {{ $bus->status_class }} rounded-full transition-all duration-300" 
                 style="width: {{ min($bus->occupancy_percentage, 100) }}%"></div>
        </div>
    </div>

    <!-- Passenger List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Penumpang</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pendamping</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bus->assignments as $index => $assignment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ $assignment->display_name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge {{ $assignment->is_guardian ? 'bg-purple-500' : 'bg-indigo-500' }} text-white">
                                    {{ $assignment->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if(!$assignment->is_guardian && $assignment->participant)
                                    <span class="badge {{ $assignment->participant->category_badge_class }} text-white">
                                        {{ $assignment->participant->category_label }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if(!$assignment->is_guardian && $assignment->participant)
                                    {{ $assignment->participant->guardian_name ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-gray-500">Belum ada penumpang di bis ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
