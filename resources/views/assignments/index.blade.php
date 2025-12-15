@extends('layouts.app')

@section('title', 'Pengelompokan Bis')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pengelompokan Bis</h1>
            <p class="text-gray-500 mt-1">Kelola penempatan peserta ke dalam bis</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('assignments.export') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </a>
            @if(auth()->user()->isAdmin())
            <form action="{{ route('assignments.reset') }}" method="POST" 
                  onsubmit="return confirm('Reset semua pengelompokan? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset Semua
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <!-- Auto Assign Panel -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold mb-2">Pengelompokan Otomatis</h2>
                <p class="text-indigo-100">Kelompokkan semua peserta secara otomatis berdasarkan kategori</p>
            </div>
            <form action="{{ route('assignments.auto') }}" method="POST" class="flex items-center gap-4">
                @csrf
                <label class="flex items-center gap-2 bg-white/10 rounded-lg px-4 py-2 cursor-pointer hover:bg-white/20 transition-colors">
                    <input type="checkbox" name="separate_guardians" value="1" {{ $separateGuardians ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-white/50 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm">Pisahkan pendamping PERFORM</span>
                </label>
                <button type="submit" class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-xl font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Kelompokkan Otomatis
                </button>
            </form>
        </div>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="bg-white/10 rounded-lg p-3">
                <p class="font-semibold text-emerald-300">✓ PERFORM (06:00)</p>
                <p class="text-indigo-200">{{ $separateGuardians ? 'Pendamping dipisahkan ke bis berbeda' : 'Anak dan pendamping di bis yang sama' }}</p>
            </div>
            <div class="bg-white/10 rounded-lg p-3">
                <p class="font-semibold text-blue-300">✓ UMUM (07:00)</p>
                <p class="text-indigo-200">Anak dan pendamping SELALU di bis yang sama</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Unassigned Participants -->
    @if($unassignedParticipants->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h2 class="text-lg font-semibold text-amber-800">Peserta Belum Ditempatkan ({{ $unassignedParticipants->count() }})</h2>
            </div>
            
            <div class="flex flex-wrap gap-2">
                @foreach($unassignedParticipants as $participant)
                    <div class="bg-white rounded-lg px-3 py-2 flex items-center gap-2 border border-amber-200">
                        <span class="badge {{ $participant->category_badge_class }} text-white text-xs">
                            {{ strtoupper(substr($participant->category, 0, 1)) }}
                        </span>
                        <span class="text-sm font-medium text-gray-700">{{ $participant->name }}</span>
                        
                        @if(auth()->user()->isAdmin())
                        <!-- Manual Assign Dropdown -->
                        <form action="{{ route('assignments.manual') }}" method="POST" class="flex items-center gap-1">
                            @csrf
                            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                            <select name="bus_id" required class="text-xs border border-gray-200 rounded px-2 py-1 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Pilih Bis</option>
                                @foreach($buses as $bus)
                                    <option value="{{ $bus->id }}">{{ $bus->name }}</option>
                                @endforeach
                            </select>
                            @if($participant->hasGuardian())
                                <label class="flex items-center gap-1 text-xs text-gray-600">
                                    <input type="checkbox" name="include_guardian" value="1" checked class="w-3 h-3">
                                    +Pdmpg
                                </label>
                            @endif
                            <button type="submit" class="text-indigo-600 hover:text-indigo-800 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Bus Grid -->
    @if($buses->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-12 border border-gray-100 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Bis</h3>
            <p class="text-gray-500 mb-4">Tambahkan bis terlebih dahulu sebelum melakukan pengelompokan</p>
            <a href="{{ route('buses.index') }}" class="btn btn-primary">Tambah Bis</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($buses as $bus)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Bus Header -->
                    <div class="p-4 {{ $bus->departure_time === '06:00' ? 'bg-emerald-50 border-b border-emerald-100' : 'bg-blue-50 border-b border-blue-100' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 {{ $bus->departure_time === '06:00' ? 'bg-emerald-500' : 'bg-blue-500' }} rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                    {{ preg_replace('/[^0-9]/', '', $bus->name) ?: '?' }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $bus->name }}</h3>
                                    <p class="text-sm {{ $bus->departure_time === '06:00' ? 'text-emerald-600' : 'text-blue-600' }}">
                                        {{ $bus->departure_time }} - {{ $bus->departure_time === '06:00' ? 'PERFORM' : 'UMUM' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold {{ $bus->isFull() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $bus->occupied_seats }}<span class="text-gray-400 text-lg">/{{ $bus->capacity }}</span>
                                </p>
                                <div class="w-24 h-2 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full {{ $bus->status_class }} rounded-full" style="width: {{ min($bus->occupancy_percentage, 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passenger List -->
                    <div class="p-4 max-h-80 overflow-y-auto">
                        @if($bus->assignments->isEmpty())
                            <p class="text-center text-gray-400 py-8">Belum ada penumpang</p>
                        @else
                            <div class="space-y-2">
                                @foreach($bus->assignments->groupBy('participant_id') as $participantId => $assignments)
                                    @php
                                        $mainAssignment = $assignments->firstWhere('is_guardian', false) ?? $assignments->first();
                                        $hasGuardian = $assignments->contains('is_guardian', true);
                                        $participant = $mainAssignment->participant;
                                    @endphp
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <span class="badge {{ $participant->category_badge_class }} text-white">
                                                {{ strtoupper(substr($participant->category, 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $participant->name }}</p>
                                                @if($hasGuardian)
                                                    <p class="text-xs text-gray-500">+ {{ $participant->guardian_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($hasGuardian)
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">+Pendamping</span>
                                            @endif
                                            @if(auth()->user()->isAdmin())
                                            <form action="{{ route('assignments.remove', $mainAssignment) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus dari bis">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
