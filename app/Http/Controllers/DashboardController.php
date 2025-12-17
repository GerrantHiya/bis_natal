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
            
            // New stats
            'guardian_count' => Participant::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->distinct('phone')
                ->count('phone'),
            'kiddies_prioritas_count' => Participant::eligibleForBus()
                ->kiddiesPrioritas()
                ->count(),
            'junior_perform_count' => Participant::perform()
                ->nonKiddiesPrioritas()
                ->count(),
            'umum_biasa_count' => Participant::umum()
                ->nonKiddiesPrioritas()
                ->count(),
        ];

        $buses = Bus::withCount('assignments')
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('stats', 'buses'));
    }
}
