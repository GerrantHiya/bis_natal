<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\BusAssignment;
use App\Models\Participant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_participants' => Participant::count(),
            'perform_count' => Participant::perform()->count(),
            'umum_count' => Participant::umum()->count(),
            'pribadi_count' => Participant::pribadi()->count(),
            'total_buses' => Bus::count(),
            'morning_buses' => Bus::morning()->count(),
            'regular_buses' => Bus::regular()->count(),
            'assigned_count' => BusAssignment::distinct('participant_id')->count('participant_id'),
            'unassigned_count' => Participant::eligibleForBus()
                ->whereDoesntHave('busAssignment')
                ->count(),
        ];

        $buses = Bus::withCount('assignments')
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('stats', 'buses'));
    }
}
