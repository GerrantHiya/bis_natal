<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bus;
use App\Models\Setting;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::withCount('assignments')
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        $defaultCapacity = Setting::getDefaultBusCapacity();

        return view('buses.index', compact('buses', 'defaultCapacity'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'departure_time' => 'required|in:06:00,06:30,07:00',
        ]);

        $bus = Bus::create($validated);

        ActivityLog::log('create', "Menambahkan bis: {$bus->name} (kapasitas: {$bus->capacity})", $bus);

        return redirect()->route('buses.index')
            ->with('success', 'Bis berhasil ditambahkan!');
    }

    public function update(Request $request, Bus $bus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'departure_time' => 'required|in:06:00,06:30,07:00',
        ]);

        $oldName = $bus->name;
        $bus->update($validated);

        ActivityLog::log('update', "Mengubah bis: {$oldName} -> {$bus->name}", $bus);

        return redirect()->route('buses.index')
            ->with('success', 'Bis berhasil diperbarui!');
    }

    public function destroy(Bus $bus)
    {
        if ($bus->assignments()->count() > 0) {
            return redirect()->route('buses.index')
                ->with('error', 'Tidak dapat menghapus bis yang sudah memiliki penumpang!');
        }

        $name = $bus->name;
        $bus->delete();

        ActivityLog::log('delete', "Menghapus bis: {$name}");

        return redirect()->route('buses.index')
            ->with('success', 'Bis berhasil dihapus!');
    }

    public function show(Bus $bus)
    {
        $bus->load(['assignments.participant', 'assignments.linkedParticipant']);
        
        return view('buses.show', compact('bus'));
    }

    public function updateDefaultCapacity(Request $request)
    {
        $validated = $request->validate([
            'default_capacity' => 'required|integer|min:1|max:100',
        ]);

        Setting::set('default_bus_capacity', $validated['default_capacity']);

        ActivityLog::log('update', "Mengubah kapasitas default bis menjadi: {$validated['default_capacity']}");

        return redirect()->route('buses.index')
            ->with('success', 'Kapasitas default berhasil diperbarui!');
    }
}
