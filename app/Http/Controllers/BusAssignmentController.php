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

            $priorityBuses = Bus::priority()->orderBy('name')->get(); // 06:30 - Kiddies Prioritas
            $morningBuses = Bus::morning()->orderBy('name')->get();   // 06:00 - PERFORM
            $regularBuses = Bus::regular()->orderBy('name')->get();   // 07:00 - UMUM
            $allBuses = $priorityBuses->merge($morningBuses)->merge($regularBuses);

            // IMPORTANT: Only include participants WITH guardian_name
            // Children without guardian cannot be assigned until guardian info is saved
            
            // Step 1: Get KIDDIES PRIORITAS participants (from both PERFORM and UMUM)
            $kiddiesPrioritasParticipants = Participant::eligibleForBus()
                ->kiddiesPrioritas()
                ->whereNotNull('guardian_name')
                ->where('guardian_name', '!=', '')
                ->orderBy('name')
                ->get();

            // Step 2: Get regular PERFORM participants (non-prioritas)
            $performParticipants = Participant::perform()
                ->nonKiddiesPrioritas()
                ->whereNotNull('guardian_name')
                ->where('guardian_name', '!=', '')
                ->orderBy('name')
                ->get();
            
            // Step 3: Get regular UMUM participants (non-prioritas)
            $umumParticipants = Participant::umum()
                ->nonKiddiesPrioritas()
                ->whereNotNull('guardian_name')
                ->where('guardian_name', '!=', '')
                ->orderBy('name')
                ->get();

            // Count skipped participants
            $skippedPerform = Participant::perform()
                ->where(function($q) {
                    $q->whereNull('guardian_name')->orWhere('guardian_name', '');
                })->count();
            
            $skippedUmum = Participant::umum()
                ->where(function($q) {
                    $q->whereNull('guardian_name')->orWhere('guardian_name', '');
                })->count();
            
            $totalSkipped = $skippedPerform + $skippedUmum;

            // Track bus assignments globally
            $busAssignments = [];
            foreach ($allBuses as $bus) {
                $busAssignments[$bus->id] = 0;
            }

            // Collect separated guardians to assign later
            $separatedGuardians = collect();

            // PRIORITY 1: Assign KIDDIES PRIORITAS to priority buses (06:30) first, then overflow to other buses
            // Always keep guardians together for priority kids
            $priorityAndOtherBuses = $priorityBuses->merge($morningBuses)->merge($regularBuses);
            $this->fillBusesWithFamilyGroups(
                $kiddiesPrioritasParticipants,
                $priorityAndOtherBuses,
                false, // Never separate guardians for priority kids
                $busAssignments,
                $separatedGuardians
            );

            // PRIORITY 2: Group by guardian and fill morning buses with regular PERFORM children
            $this->fillBusesWithFamilyGroups(
                $performParticipants,
                $morningBuses,
                $separatePerformGuardians,
                $busAssignments,
                $separatedGuardians
            );

            // PRIORITY 3: Group by guardian and fill regular buses with UMUM (guardians always together)
            $this->fillBusesWithFamilyGroups(
                $umumParticipants,
                $regularBuses,
                false, // UMUM never separate guardians
                $busAssignments,
                $separatedGuardians
            );

            // If separating PERFORM guardians, assign them to ANY available bus (morning or regular)
            if ($separatePerformGuardians && $separatedGuardians->isNotEmpty()) {
                $this->assignSeparatedGuardians($separatedGuardians, $allBuses, $busAssignments);
            }

            DB::commit();

            $totalAssigned = BusAssignment::count();
            ActivityLog::log('auto_assign', "Pengelompokan otomatis: {$totalAssigned} penumpang ditempatkan, {$totalSkipped} ditunda (belum ada pendamping)");

            $message = "Pengelompokan otomatis berhasil! {$totalAssigned} penumpang ditempatkan.";
            if ($totalSkipped > 0) {
                $message .= " {$totalSkipped} anak belum diassign karena pendamping belum terdaftar.";
            }

            return redirect()->route('assignments.index')
                ->with('success', $message);

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
     * 
     * IMPORTANT: No force-assign - if a group doesn't fit anywhere, they stay unassigned
     * This prevents overloading buses.
     */
    private function fillBusesWithFamilyGroups($participants, $buses, $separateGuardians, &$busAssignments, &$separatedGuardians)
    {
        if ($buses->isEmpty() || $participants->isEmpty()) return;

        // Step 1: Group participants by guardian
        // Priority: phone number (if exists), then guardian_name
        $familyGroups = $this->groupByGuardian($participants);

        $currentBusIndex = 0;
        $busCount = $buses->count();

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
            $attempts = 0;
            
            while (!$assigned && $attempts < $busCount) {
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
                            // Track guardian to assign later (to any available bus)
                            $separatedGuardians->push([
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
                }
                $attempts++;
            }
            
            // If not assigned, the group stays unassigned (NO FORCE-ASSIGN = NO OVERLOAD)
            // They will appear in "Peserta Belum Ditempatkan" instead
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
