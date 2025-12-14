<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bus;
use App\Models\BusAssignment;
use App\Models\Participant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusAssignmentController extends Controller
{
    public function index()
    {
        $buses = Bus::with(['assignments.participant', 'assignments.linkedParticipant'])
            ->withCount('assignments')
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        $unassignedParticipants = Participant::eligibleForBus()
            ->whereDoesntHave('busAssignment')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $separateGuardians = Setting::shouldSeparatePerformGuardians();

        return view('assignments.index', compact('buses', 'unassignedParticipants', 'separateGuardians'));
    }

    public function autoAssign(Request $request)
    {
        $separatePerformGuardians = $request->boolean('separate_guardians', false);
        
        // Save setting
        Setting::set('separate_perform_guardians', $separatePerformGuardians);

        try {
            DB::beginTransaction();
            
            // Clear existing assignments using delete() instead of truncate()
            BusAssignment::query()->delete();

            // Get all buses ordered by name
            $morningBuses = Bus::morning()->orderBy('name')->get();
            $regularBuses = Bus::regular()->orderBy('name')->get();

            // Get participants by category (exclude PRIBADI)
            $performParticipants = Participant::perform()->orderBy('name')->get();
            $umumParticipants = Participant::umum()->orderBy('name')->get();

            // === ASSIGN PERFORM PARTICIPANTS (06:00) ===
            // Fill buses to FULL capacity before moving to next bus
            $this->fillBusesToCapacity(
                $performParticipants,
                $morningBuses,
                $separatePerformGuardians
            );

            // === ASSIGN UMUM PARTICIPANTS (07:00) ===
            // UMUM must always be with guardian (never separate)
            $this->fillBusesToCapacity(
                $umumParticipants,
                $regularBuses,
                false // Never separate guardians for UMUM
            );

            DB::commit();

            $totalAssigned = BusAssignment::count();
            ActivityLog::log('auto_assign', "Pengelompokan otomatis: {$totalAssigned} penumpang ditempatkan");

            return redirect()->route('assignments.index')
                ->with('success', 'Pengelompokan otomatis berhasil! Bis diisi penuh sebelum pindah ke bis berikutnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('assignments.index')
                ->with('error', 'Gagal melakukan pengelompokan: ' . $e->getMessage());
        }
    }

    /**
     * Fill buses to FULL capacity before moving to the next bus.
     * If not enough participants, remaining buses stay empty.
     */
    private function fillBusesToCapacity($participants, $buses, $separateGuardians)
    {
        if ($buses->isEmpty() || $participants->isEmpty()) return;

        $currentBusIndex = 0;
        $busCount = $buses->count();
        
        // Track assignments per bus
        $busAssignments = [];
        foreach ($buses as $bus) {
            $busAssignments[$bus->id] = 0;
        }

        // For separated guardians (PERFORM), we need to track them separately
        $guardiansToAssignLater = collect();

        foreach ($participants as $participant) {
            // Calculate seats needed for this participant
            $seatsNeeded = 1; // Child seat
            if ($participant->hasGuardian() && !$separateGuardians) {
                $seatsNeeded = 2; // Child + Guardian together
            }

            // Find the first bus with enough capacity
            $assigned = false;
            $startIndex = $currentBusIndex;
            
            do {
                $bus = $buses[$currentBusIndex];
                $remainingCapacity = $bus->capacity - $busAssignments[$bus->id];

                if ($remainingCapacity >= $seatsNeeded) {
                    // Assign child to this bus
                    BusAssignment::create([
                        'bus_id' => $bus->id,
                        'participant_id' => $participant->id,
                        'is_guardian' => false,
                    ]);
                    $busAssignments[$bus->id]++;

                    // Assign guardian if not separating
                    if ($participant->hasGuardian() && !$separateGuardians) {
                        BusAssignment::create([
                            'bus_id' => $bus->id,
                            'participant_id' => $participant->id,
                            'is_guardian' => true,
                            'linked_participant_id' => $participant->id,
                        ]);
                        $busAssignments[$bus->id]++;
                    } elseif ($participant->hasGuardian() && $separateGuardians) {
                        // Track guardian to assign later
                        $guardiansToAssignLater->push($participant);
                    }

                    $assigned = true;

                    // Check if current bus is now FULL, move to next bus
                    if ($busAssignments[$bus->id] >= $bus->capacity) {
                        $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                    }
                } else {
                    // Current bus can't fit, move to next bus
                    $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                    
                    // If we've checked all buses and came back, all buses are full
                    if ($currentBusIndex == $startIndex) {
                        break;
                    }
                }
            } while (!$assigned && $currentBusIndex != $startIndex);

            // If still not assigned (all buses full), force assign to first bus with any space
            if (!$assigned) {
                foreach ($buses as $bus) {
                    if ($busAssignments[$bus->id] < $bus->capacity) {
                        BusAssignment::create([
                            'bus_id' => $bus->id,
                            'participant_id' => $participant->id,
                            'is_guardian' => false,
                        ]);
                        $busAssignments[$bus->id]++;
                        
                        if ($participant->hasGuardian() && !$separateGuardians) {
                            BusAssignment::create([
                                'bus_id' => $bus->id,
                                'participant_id' => $participant->id,
                                'is_guardian' => true,
                                'linked_participant_id' => $participant->id,
                            ]);
                            $busAssignments[$bus->id]++;
                        }
                        break;
                    }
                }
            }
        }

        // If separating guardians (PERFORM), assign them to fill remaining capacity
        if ($separateGuardians && $guardiansToAssignLater->isNotEmpty()) {
            $this->assignGuardiansToFillBuses($guardiansToAssignLater, $buses, $busAssignments);
        }
    }

    /**
     * Assign guardians to fill up buses to capacity (for PERFORM when separating)
     */
    private function assignGuardiansToFillBuses($guardians, $buses, &$busAssignments)
    {
        $currentBusIndex = 0;
        $busCount = $buses->count();

        foreach ($guardians as $participant) {
            $assigned = false;
            $attempts = 0;

            while (!$assigned && $attempts < $busCount) {
                $bus = $buses[$currentBusIndex];
                
                if ($busAssignments[$bus->id] < $bus->capacity) {
                    BusAssignment::create([
                        'bus_id' => $bus->id,
                        'participant_id' => $participant->id,
                        'is_guardian' => true,
                        'linked_participant_id' => $participant->id,
                    ]);
                    $busAssignments[$bus->id]++;
                    $assigned = true;

                    // Move to next bus if current is full
                    if ($busAssignments[$bus->id] >= $bus->capacity) {
                        $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                    }
                } else {
                    $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                }
                $attempts++;
            }
        }
    }

    public function manualAssign(Request $request)
    {
        $validated = $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'bus_id' => 'required|exists:buses,id',
            'include_guardian' => 'boolean',
        ]);

        $participant = Participant::findOrFail($validated['participant_id']);
        $bus = Bus::findOrFail($validated['bus_id']);

        // Remove existing assignments
        BusAssignment::where('participant_id', $participant->id)->delete();

        // Create new assignment
        BusAssignment::create([
            'bus_id' => $bus->id,
            'participant_id' => $participant->id,
            'is_guardian' => false,
        ]);

        // Add guardian if requested
        if ($request->boolean('include_guardian') && $participant->hasGuardian()) {
            BusAssignment::create([
                'bus_id' => $bus->id,
                'participant_id' => $participant->id,
                'is_guardian' => true,
                'linked_participant_id' => $participant->id,
            ]);
        }

        ActivityLog::log('update', "Memindahkan peserta {$participant->name} ke {$bus->name}", $participant);

        return redirect()->route('assignments.index')
            ->with('success', "Peserta {$participant->name} berhasil dipindahkan ke {$bus->name}!");
    }

    public function removeAssignment(BusAssignment $assignment)
    {
        $participantId = $assignment->participant_id;
        
        // Remove both child and guardian assignments
        BusAssignment::where('participant_id', $participantId)->delete();

        $participant = Participant::find($participantId);
        ActivityLog::log('delete', "Menghapus peserta dari bis: " . ($participant?->name ?? 'Unknown'), $participant);

        return redirect()->route('assignments.index')
            ->with('success', 'Peserta berhasil dihapus dari bis!');
    }

    public function reset()
    {
        $count = BusAssignment::count();
        BusAssignment::query()->delete();

        ActivityLog::log('reset', "Mereset semua pengelompokan bis ({$count} penempatan dihapus)");

        return redirect()->route('assignments.index')
            ->with('success', 'Semua pengelompokan berhasil direset!');
    }

    public function export()
    {
        $buses = Bus::with(['assignments.participant', 'assignments.linkedParticipant'])
            ->orderBy('departure_time')
            ->orderBy('name')
            ->get();

        return view('assignments.export', compact('buses'));
    }
}
