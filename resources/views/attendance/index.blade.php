@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Absensi</h1>
            <p class="text-gray-500 mt-1">Pantau kehadiran peserta di setiap bis</p>
        </div>
    </div>

    <!-- Bus List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($buses as $bus)
            <a href="{{ route('attendance.show', $bus) }}" 
               class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 @if($bus->departure_time === '06:00') bg-emerald-100 text-emerald-600 @elseif($bus->departure_time === '06:30') bg-amber-100 text-amber-600 @else bg-blue-100 text-blue-600 @endif rounded-xl flex items-center justify-center font-bold text-lg">
                            {{ preg_replace('/[^0-9]/', '', $bus->name) ?: '?' }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $bus->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $bus->departure_time_label }}</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>

                <!-- Progress Bars -->
                <div class="space-y-3">
                    <!-- Berangkat -->
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-600">🚌 Berangkat</span>
                            <span class="font-medium {{ $bus->berangkat_count >= $bus->total_passengers ? 'text-emerald-600' : 'text-gray-700' }}">
                                {{ $bus->berangkat_count }} / {{ $bus->total_passengers }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" 
                                 style="width: {{ $bus->total_passengers > 0 ? ($bus->berangkat_count / $bus->total_passengers * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Pulang -->
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-600">🏠 Pulang</span>
                            <span class="font-medium {{ $bus->pulang_count >= $bus->total_passengers ? 'text-blue-600' : 'text-gray-700' }}">
                                {{ $bus->pulang_count }} / {{ $bus->total_passengers }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-300" 
                                 style="width: {{ $bus->total_passengers > 0 ? ($bus->pulang_count / $bus->total_passengers * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-500">Belum ada bis yang ditambahkan</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
