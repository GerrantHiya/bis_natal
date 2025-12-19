@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500 mt-1 text-sm sm:text-base">Ringkasan pengelompokan bis Natal Sekolah Minggu</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('attendance.index') }}" class="btn bg-emerald-600 p-2 rounded text-white hover:bg-emerald-700 text-sm sm:text-base">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Absensi
            </a>
            <a href="{{ route('assignments.index') }}" class="btn btn-primary text-sm sm:text-base">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Pengelompokan
            </a>
        </div>
    </div>

    <!-- Detail Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pendamping -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['guardian_count'] }}</p>
                    <p class="text-sm text-gray-500">Orang Tua/ Pendamping</p>
                </div>
            </div>
        </div>

        <!-- Kiddies Prioritas (06:30) -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-amber-200 bg-amber-50/30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">⭐</span>
                </div>
                <div>
                    <p class="text-3xl font-bold text-amber-600">{{ $stats['kiddies_prioritas_count'] }}</p>
                    <p class="text-sm text-gray-500">Kiddies Perform (06:30)</p>
                </div>
            </div>
        </div>

        <!-- Junior Perform (06:00) -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-emerald-200 bg-emerald-50/30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-emerald-600">{{ $stats['junior_perform_count'] }}</p>
                    <p class="text-sm text-gray-500">Junior Perform (06:00)</p>
                </div>
            </div>
        </div>

        <!-- Umum Biasa (07:00) -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-blue-200 bg-blue-50/30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['umum_biasa_count'] }}</p>
                    <p class="text-sm text-gray-500">Umum Biasa (07:00)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Status & Bus Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Assignment Status -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Pengelompokan</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">Sudah Ditempatkan</span>
                    </div>
                    <span class="text-2xl font-bold text-emerald-600">{{ $stats['assigned_count'] }}</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">Belum Ditempatkan</span>
                    </div>
                    <span class="text-2xl font-bold text-amber-600">{{ $stats['unassigned_count'] }}</span>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="{{ route('assignments.index') }}" class="btn btn-primary w-full">
                    Kelola Pengelompokan
                </a>
            </div>
        </div>

        <!-- Bus Overview -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Bis</h2>
                <a href="{{ route('buses.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                    Kelola Bis →
                </a>
            </div>
            
            @if($buses->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <p class="text-gray-500">Belum ada bis yang ditambahkan</p>
                    <a href="{{ route('buses.index') }}" class="btn btn-primary mt-4">Tambah Bis</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($buses as $bus)
                        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-indigo-200 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 @if($bus->departure_time === '06:00') bg-emerald-100 text-emerald-600 @elseif($bus->departure_time === '06:30') bg-amber-100 text-amber-600 @else bg-blue-100 text-blue-600 @endif rounded-xl flex items-center justify-center font-bold">
                                    {{ preg_replace('/[^0-9]/', '', $bus->name) ?: '?' }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $bus->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $bus->departure_time_label }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $bus->occupied_seats >= $bus->capacity ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $bus->occupied_seats }} / {{ $bus->capacity }}
                                </p>
                                <div class="w-24 h-2 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full {{ $bus->status_class }} rounded-full transition-all duration-300" 
                                         style="width: {{ min($bus->occupancy_percentage, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
