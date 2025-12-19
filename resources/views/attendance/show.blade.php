@extends('layouts.app')

@section('title', 'Absensi ' . $bus->name)

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('attendance.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2 mb-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl sm:text-3xl font-bold text-gray-900">Absensi {{ $bus->name }}</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ $bus->departure_time_label }} • {{ $passengers->count() }} penumpang</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
        <div class="bg-emerald-50 rounded-xl p-3 sm:p-4 border border-emerald-200">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="text-xl sm:text-2xl">🚌</span>
                <div>
                    <p class="font-semibold text-emerald-800 text-sm sm:text-base">Berangkat</p>
                    <p class="text-xs sm:text-sm text-emerald-600">
                        <span id="berangkat-count">0</span> / {{ $passengers->count() }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 rounded-xl p-3 sm:p-4 border border-blue-200">
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="text-xl sm:text-2xl">🏠</span>
                <div>
                    <p class="font-semibold text-blue-800 text-sm sm:text-base">Pulang</p>
                    <p class="text-xs sm:text-sm text-blue-600">
                        <span id="pulang-count">0</span> / {{ $passengers->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Cards / Desktop Table -->
    <div class="space-y-3 sm:hidden">
        @foreach($passengers as $index => $assignment)
            @php
                $berangkat = $assignment->attendances->where('type', 'berangkat')->first();
                $pulang = $assignment->attendances->where('type', 'pulang')->first();
                $guardianAssignment = $bus->assignments->where('is_guardian', true)->where('linked_participant_id', $assignment->participant_id)->first();
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900">{{ $assignment->participant->name }}</span>
                            @if($assignment->participant->is_kiddies_prioritas)
                                <span class="text-amber-500">⭐</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $assignment->participant->guardian_name ?: 'Tanpa pendamping' }}</p>
                    </div>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">#{{ $index + 1 }}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-2">
                    <!-- Berangkat -->
                    <div class="flex items-center justify-between p-2 rounded-lg {{ $berangkat && $berangkat->checked_at ? 'bg-emerald-50' : 'bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="attendance-checkbox sr-only peer"
                                       data-assignment-id="{{ $assignment->id }}"
                                       data-type="berangkat"
                                       {{ $berangkat && $berangkat->checked_at ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                            <span class="text-xs font-medium text-gray-700">🚌</span>
                        </div>
                        <span class="text-xs text-gray-500 timestamp-berangkat-{{ $assignment->id }}">
                            {{ $berangkat && $berangkat->checked_at ? $berangkat->checked_at->format('H:i') : '-' }}
                        </span>
                    </div>
                    
                    <!-- Pulang -->
                    <div class="flex items-center justify-between p-2 rounded-lg {{ $pulang && $pulang->checked_at ? 'bg-blue-50' : 'bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="attendance-checkbox sr-only peer"
                                       data-assignment-id="{{ $assignment->id }}"
                                       data-type="pulang"
                                       {{ $pulang && $pulang->checked_at ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-xs font-medium text-gray-700">🏠</span>
                        </div>
                        <span class="text-xs text-gray-500 timestamp-pulang-{{ $assignment->id }}">
                            {{ $pulang && $pulang->checked_at ? $pulang->checked_at->format('H:i') : '-' }}
                        </span>
                    </div>
                </div>

                @if($guardianAssignment)
                    @php
                        $guardianBerangkat = $guardianAssignment->attendances->where('type', 'berangkat')->first();
                        $guardianPulang = $guardianAssignment->attendances->where('type', 'pulang')->first();
                    @endphp
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-purple-600 font-medium mb-2">👤 Pendamping: {{ $assignment->participant->guardian_name }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="flex items-center justify-between p-2 rounded-lg {{ $guardianBerangkat && $guardianBerangkat->checked_at ? 'bg-emerald-50' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="attendance-checkbox sr-only peer"
                                               data-assignment-id="{{ $guardianAssignment->id }}"
                                               data-type="berangkat"
                                               {{ $guardianBerangkat && $guardianBerangkat->checked_at ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                    <span class="text-xs text-gray-700">🚌</span>
                                </div>
                                <span class="text-xs text-gray-400 timestamp-berangkat-{{ $guardianAssignment->id }}">
                                    {{ $guardianBerangkat && $guardianBerangkat->checked_at ? $guardianBerangkat->checked_at->format('H:i') : '-' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-lg {{ $guardianPulang && $guardianPulang->checked_at ? 'bg-blue-50' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="attendance-checkbox sr-only peer"
                                               data-assignment-id="{{ $guardianAssignment->id }}"
                                               data-type="pulang"
                                               {{ $guardianPulang && $guardianPulang->checked_at ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                    <span class="text-xs text-gray-700">🏠</span>
                                </div>
                                <span class="text-xs text-gray-400 timestamp-pulang-{{ $guardianAssignment->id }}">
                                    {{ $guardianPulang && $guardianPulang->checked_at ? $guardianPulang->checked_at->format('H:i') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Desktop Table (hidden on mobile) -->
    <div class="hidden sm:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pendamping</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-emerald-600 uppercase bg-emerald-50">🚌 Berangkat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-blue-600 uppercase bg-blue-50">🏠 Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($passengers as $index => $assignment)
                        @php
                            $berangkat = $assignment->attendances->where('type', 'berangkat')->first();
                            $pulang = $assignment->attendances->where('type', 'pulang')->first();
                            $guardianAssignment = $bus->assignments->where('is_guardian', true)->where('linked_participant_id', $assignment->participant_id)->first();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 text-sm">{{ $assignment->participant->name }}</span>
                                    @if($assignment->participant->is_kiddies_prioritas)
                                        <span class="text-amber-500">⭐</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $assignment->participant->guardian_name ?: '-' }}</td>
                            <td class="px-4 py-3 text-center bg-emerald-50/50">
                                <div class="flex flex-col items-center gap-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="attendance-checkbox sr-only peer" data-assignment-id="{{ $assignment->id }}" data-type="berangkat" {{ $berangkat && $berangkat->checked_at ? 'checked' : '' }}>
                                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                    <span class="text-xs text-gray-500 timestamp-berangkat-{{ $assignment->id }}">{{ $berangkat && $berangkat->checked_at ? $berangkat->checked_at->format('H:i:s') : '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center bg-blue-50/50">
                                <div class="flex flex-col items-center gap-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="attendance-checkbox sr-only peer" data-assignment-id="{{ $assignment->id }}" data-type="pulang" {{ $pulang && $pulang->checked_at ? 'checked' : '' }}>
                                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                    <span class="text-xs text-gray-500 timestamp-pulang-{{ $assignment->id }}">{{ $pulang && $pulang->checked_at ? $pulang->checked_at->format('H:i:s') : '-' }}</span>
                                </div>
                            </td>
                        </tr>
                        @if($guardianAssignment)
                            @php
                                $guardianBerangkat = $guardianAssignment->attendances->where('type', 'berangkat')->first();
                                $guardianPulang = $guardianAssignment->attendances->where('type', 'pulang')->first();
                            @endphp
                            <tr class="bg-purple-50/30 hover:bg-purple-50">
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2" colspan="2">
                                    <div class="flex items-center gap-2 pl-4">
                                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Pendamping</span>
                                        <span class="text-sm text-gray-700">{{ $assignment->participant->guardian_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center bg-emerald-50/30">
                                    <div class="flex flex-col items-center gap-1">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="attendance-checkbox sr-only peer" data-assignment-id="{{ $guardianAssignment->id }}" data-type="berangkat" {{ $guardianBerangkat && $guardianBerangkat->checked_at ? 'checked' : '' }}>
                                            <div class="w-9 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                        <span class="text-xs text-gray-400 timestamp-berangkat-{{ $guardianAssignment->id }}">{{ $guardianBerangkat && $guardianBerangkat->checked_at ? $guardianBerangkat->checked_at->format('H:i:s') : '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center bg-blue-50/30">
                                    <div class="flex flex-col items-center gap-1">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="attendance-checkbox sr-only peer" data-assignment-id="{{ $guardianAssignment->id }}" data-type="pulang" {{ $guardianPulang && $guardianPulang->checked_at ? 'checked' : '' }}>
                                            <div class="w-9 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                        <span class="text-xs text-gray-400 timestamp-pulang-{{ $guardianAssignment->id }}">{{ $guardianPulang && $guardianPulang->checked_at ? $guardianPulang->checked_at->format('H:i:s') : '-' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.attendance-checkbox');
    updateCounts();
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const assignmentId = this.dataset.assignmentId;
            const type = this.dataset.type;
            
            fetch('{{ route('attendance.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ bus_assignment_id: assignmentId, type: type })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll(`.timestamp-${type}-${assignmentId}`).forEach(el => {
                        el.textContent = data.checked_at || '-';
                    });
                    updateCounts();
                } else {
                    this.checked = !this.checked;
                    alert('Gagal memperbarui absensi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked;
                alert('Terjadi kesalahan');
            });
        });
    });
    
    function updateCounts() {
        const berangkatChecked = document.querySelectorAll('.attendance-checkbox[data-type="berangkat"]:checked').length;
        const pulangChecked = document.querySelectorAll('.attendance-checkbox[data-type="pulang"]:checked').length;
        document.getElementById('berangkat-count').textContent = berangkatChecked;
        document.getElementById('pulang-count').textContent = pulangChecked;
    }
});
</script>
@endpush
@endsection
