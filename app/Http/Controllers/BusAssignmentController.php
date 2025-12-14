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
        
        Setting::set('separate_perform_guardians', $separatePerformGuardians);

        try {
            DB::beginTransaction();
            
            BusAssignment::query()->delete();

            $morningBuses = Bus::morning()->orderBy('name')->get();
            $regularBuses = Bus::regular()->orderBy('name')->get();

            $performParticipants = Participant::perform()->orderBy('name')->get();
            $umumParticipants = Participant::umum()->orderBy('name')->get();

            // Group by guardian and fill buses
            $this->fillBusesWithFamilyGroups(
                $performParticipants,
                $morningBuses,
                $separatePerformGuardians
            );

            $this->fillBusesWithFamilyGroups(
                $umumParticipants,
                $regularBuses,
                false // UMUM never separate guardians
            );

            DB::commit();

            $totalAssigned = BusAssignment::count();
            ActivityLog::log('auto_assign', "Pengelompokan otomatis: {$totalAssigned} penumpang ditempatkan");

            return redirect()->route('assignments.index')
                ->with('success', 'Pengelompokan otomatis berhasil! Keluarga dengan pendamping yang sama ditempatkan di bis yang sama.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('assignments.index')
                ->with('error', 'Gagal melakukan pengelompokan: ' . $e->getMessage());
        }
    }

    /**
     * Group participants by guardian (using phone or guardian_name), 
     * then assign each family group to the same bus.
     * 1 guardian can have multiple children.
     */
    private function fillBusesWithFamilyGroups($participants, $buses, $separateGuardians)
    {
        if ($buses->isEmpty() || $participants->isEmpty()) return;

        // Step 1: Group participants by guardian
        // Priority: phone number (if exists), then guardian_name
        $familyGroups = $this->groupByGuardian($participants);

        $currentBusIndex = 0;
        $busCount = $buses->count();
        
        $busAssignments = [];
        foreach ($buses as $bus) {
            $busAssignments[$bus->id] = 0;
        }

        // For separated guardians (PERFORM), track guardians to assign later
        $guardiansToAssignLater = collect();

        // Step 2: Assign each family group to a bus
        foreach ($familyGroups as $group) {
            $children = $group['children'];
            $hasGuardian = $group['has_guardian'];
            $guardianKey = $group['guardian_key'];
            
            // Calculate seats needed:
            // - All children in this group
            // - Plus 1 guardian (if they have one and not separating)
            $seatsNeeded = $children->count();
            if ($hasGuardian && !$separateGuardians) {
                $seatsNeeded += 1; // Only 1 guardian for the entire group
            }

            // Find a bus with enough capacity
            $assigned = false;
            $startIndex = $currentBusIndex;
            
            do {
                $bus = $buses[$currentBusIndex];
                $remainingCapacity = $bus->capacity - $busAssignments[$bus->id];

                if ($remainingCapacity >= $seatsNeeded) {
                    // Assign all children in this group to this bus
                    foreach ($children as $child) {
                        BusAssignment::create([
                            'bus_id' => $bus->id,
                            'participant_id' => $child->id,
                            'is_guardian' => false,
                        ]);
                        $busAssignments[$bus->id]++;
                    }

                    // Assign guardian (only once for the whole group)
                    if ($hasGuardian) {
                        if (!$separateGuardians) {
                            // Use first child as the linked participant for guardian
                            $firstChild = $children->first();
                            BusAssignment::create([
                                'bus_id' => $bus->id,
                                'participant_id' => $firstChild->id,
                                'is_guardian' => true,
                                'linked_participant_id' => $firstChild->id,
                            ]);
                            $busAssignments[$bus->id]++;
                        } else {
                            // Track guardian to assign later (for PERFORM)
                            $guardiansToAssignLater->push([
                                'guardian_key' => $guardianKey,
                                'guardian_name' => $children->first()->guardian_name,
                                'first_child_id' => $children->first()->id,
                            ]);
                        }
                    }

                    $assigned = true;

                    // Move to next bus if current is full
                    if ($busAssignments[$bus->id] >= $bus->capacity) {
                        $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                    }
                } else {
                    // Move to next bus
                    $currentBusIndex = ($currentBusIndex + 1) % $busCount;
                    
                    if ($currentBusIndex == $startIndex) {
                        break; // All buses checked
                    }
                }
            } while (!$assigned && $currentBusIndex != $startIndex);

            // Force assign if all buses are "full" but we still need to place
            if (!$assigned) {
                foreach ($buses as $bus) {
                    // Assign children
                    foreach ($children as $child) {
                        BusAssignment::create([
                            'bus_id' => $bus->id,
                            'participant_id' => $child->id,
                            'is_guardian' => false,
                        ]);
                        $busAssignments[$bus->id]++;
                    }
                    
                    // Assign guardian
                    if ($hasGuardian && !$separateGuardians) {
                        $firstChild = $children->first();
                        BusAssignment::create([
                            'bus_id' => $bus->id,
                            'participant_id' => $firstChild->id,
                            'is_guardian' => true,
                            'linked_participant_id' => $firstChild->id,
                        ]);
                        $busAssignments[$bus->id]++;
                    }
                    break;
                }
            }
        }

        // Step 3: Assign separated guardians (for PERFORM)
        if ($separateGuardians && $guardiansToAssignLater->isNotEmpty()) {
            $this->assignSeparatedGuardians($guardiansToAssignLater, $buses, $busAssignments);
        }
    }

    /**
     * Group participants by their guardian.
     * Uses phone number first (if available), otherwise guardian_name.
     * Children without guardians are put in individual groups.
     */
    private function groupByGuardian($participants)
    {
        $groups = [];
        $noGuardianIndex = 0;

        foreach ($participants as $participant) {
            if (!$participant->hasGuardian()) {
                // No guardian - individual group
                $key = '_no_guardian_' . ($noGuardianIndex++);
                $groups[$key] = [
                    'guardian_key' => $key,
                    'has_guardian' => false,
                    'children' => collect([$participant]),
                ];
            } else {
                // Has guardian - group by phone or name
                $key = $this->getGuardianKey($participant);
                
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'guardian_key' => $key,
                        'has_guardian' => true,
                        'children' => collect(),
                    ];
                }
                $groups[$key]['children']->push($participant);
            }
        }

        // Sort groups by size (larger groups first to fill buses better)
        usort($groups, function($a, $b) {
            return $b['children']->count() - $a['children']->count();
        });

        return $groups;
    }

    /**
     * Get a unique key for grouping by guardian.
     * Priority: phone (normalized), then guardian_name (lowercase trimmed)
     */
    private function getGuardianKey($participant)
    {
        // Use phone if available
        if (!empty($participant->phone)) {
            // Normalize phone number
            $phone = preg_replace('/[^0-9]/', '', $participant->phone);
            if (!empty($phone)) {
                return 'phone_' . $phone;
            }
        }
        
        // Fall back to guardian name
        if (!empty($participant->guardian_name)) {
            return 'name_' . strtolower(trim($participant->guardian_name));
        }
        
        // Return participant ID if nothing else
        return 'participant_' . $participant->id;
    }

    /**
     * Assign separated guardians to fill up buses (for PERFORM)
     */
    private function assignSeparatedGuardians($guardians, $buses, &$busAssignments)
    {
        $currentBusIndex = 0;
        $busCount = $buses->count();
        
        // Unique guardians only (by guardian_key)
        $uniqueGuardians = $guardians->unique('guardian_key');

        foreach ($uniqueGuardians as $guardianInfo) {
            $assigned = false;
            $attempts = 0;

            while (!$assigned && $attempts < $busCount) {
                $bus = $buses[$currentBusIndex];
                
                if ($busAssignments[$bus->id] < $bus->capacity) {
                    BusAssignment::create([
                        'bus_id' => $bus->id,
                        'participant_id' => $guardianInfo['first_child_id'],
                        'is_guardian' => true,
                        'linked_participant_id' => $guardianInfo['first_child_id'],
                    ]);
                    $busAssignments[$bus->id]++;
                    $assigned = true;

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
