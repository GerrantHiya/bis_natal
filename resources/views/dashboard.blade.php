@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500 mt-1">Ringkasan pengelompokan bis Natal Sekolah Minggu</p>
        </div>
        <a href="{{ route('assignments.index') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Lihat Pengelompokan
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Peserta -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_participants'] }}</p>
                    <p class="text-sm text-gray-500">Total Peserta</p>
                </div>
            </div>
        </div>

        <!-- PERFORM -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['perform_count'] }}</p>
                    <p class="text-sm text-gray-500">PERFORM (06:00)</p>
                </div>
            </div>
        </div>

        <!-- UMUM -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['umum_count'] }}</p>
                    <p class="text-sm text-gray-500">UMUM (07:00)</p>
                </div>
            </div>
        </div>

        <!-- PRIBADI -->
        <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pribadi_count'] }}</p>
                    <p class="text-sm text-gray-500">PRIBADI (Tidak Bis)</p>
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
                                <div class="w-12 h-12 {{ $bus->departure_time === '06:00' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }} rounded-xl flex items-center justify-center font-bold">
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

    <!-- Quick Actions -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 text-white">
        <h2 class="text-2xl font-bold mb-2">Langkah Berikutnya</h2>
        <p class="text-indigo-100 mb-6">Ikuti langkah-langkah berikut untuk mengelompokkan peserta bis:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('participants.index') }}" class="bg-white/10 hover:bg-white/20 rounded-xl p-4 transition-colors">
                <div class="text-3xl mb-2">1️⃣</div>
                <h3 class="font-semibold">Import Data Peserta</h3>
                <p class="text-sm text-indigo-200 mt-1">Upload file Excel peserta</p>
            </a>
            
            <a href="{{ route('buses.index') }}" class="bg-white/10 hover:bg-white/20 rounded-xl p-4 transition-colors">
                <div class="text-3xl mb-2">2️⃣</div>
                <h3 class="font-semibold">Tambah Bis</h3>
                <p class="text-sm text-indigo-200 mt-1">Atur jumlah dan kapasitas bis</p>
            </a>
            
            <a href="{{ route('assignments.index') }}" class="bg-white/10 hover:bg-white/20 rounded-xl p-4 transition-colors">
                <div class="text-3xl mb-2">3️⃣</div>
                <h3 class="font-semibold">Kelompokkan Otomatis</h3>
                <p class="text-sm text-indigo-200 mt-1">Jalankan pengelompokan otomatis</p>
            </a>
        </div>
    </div>
</div>
@endsection
