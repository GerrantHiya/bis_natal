<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Bus;
use App\Models\BusAssignment;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $buses = Bus::with(['assignments.participant', 'assignments.attendances'])
            ->withCount('assignments')
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        // Calculate attendance stats per bus
        foreach ($buses as $bus) {
            $totalAssignments = $bus->assignments->count();
            $berangkatCount = 0;
            $pulangCount = 0;

            foreach ($bus->assignments as $assignment) {
                if ($assignment->attendances->where('type', 'berangkat')->where('checked_at', '!=', null)->count() > 0) {
                    $berangkatCount++;
                }
                if ($assignment->attendances->where('type', 'pulang')->where('checked_at', '!=', null)->count() > 0) {
                    $pulangCount++;
                }
            }

            $bus->berangkat_count = $berangkatCount;
            $bus->pulang_count = $pulangCount;
            $bus->total_passengers = $totalAssignments;
        }

        return view('attendance.index', compact('buses'));
    }

    public function show(Bus $bus)
    {
        $bus->load(['assignments.participant', 'assignments.linkedParticipant', 'assignments.attendances']);

        // Group assignments - main passengers only (not guardians), sorted by name with reindexed keys
        $passengers = $bus->assignments->where('is_guardian', false)->sortBy(function($assignment) {
            return $assignment->participant->name;
        })->values();

        return view('attendance.show', compact('bus', 'passengers'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'bus_assignment_id' => 'required|exists:bus_assignments,id',
            'type' => 'required|in:berangkat,pulang',
        ]);

        $attendance = Attendance::firstOrCreate(
            [
                'bus_assignment_id' => $validated['bus_assignment_id'],
                'type' => $validated['type'],
            ]
        );

        // Toggle the checked_at
        if ($attendance->checked_at) {
            // Cancel attendance
            $attendance->checked_at = null;
            $attendance->checked_by = null;
            $attendance->save();
            
            return response()->json([
                'success' => true,
                'checked' => false,
                'checked_at' => null,
                'message' => 'Absensi dibatalkan',
            ]);
        } else {
            // Mark attendance
            $attendance->checked_at = now();
            $attendance->checked_by = auth()->id();
            $attendance->save();
            
            return response()->json([
                'success' => true,
                'checked' => true,
                'checked_at' => $attendance->checked_at->format('H:i:s'),
                'message' => 'Absensi berhasil dicatat',
            ]);
        }
    }
}
