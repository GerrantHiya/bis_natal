<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = Participant::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('guardian_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $participants = $query->orderBy('name')->paginate(20);

        return view('participants.index', compact('participants'));
    }

    public function create()
    {
        return view('participants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:1|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'category' => 'required|in:perform,umum,pribadi',
        ]);

        $participant = Participant::create($validated);

        ActivityLog::log('create', "Menambahkan peserta: {$participant->name}", $participant);

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil ditambahkan!');
    }

    public function edit(Participant $participant)
    {
        return view('participants.edit', compact('participant'));
    }

    public function update(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:1|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'category' => 'required|in:perform,umum,pribadi',
        ]);

        $oldName = $participant->name;
        $participant->update($validated);

        ActivityLog::log('update', "Mengubah peserta: {$oldName}", $participant);

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil diperbarui!');
    }

    public function destroy(Participant $participant)
    {
        $name = $participant->name;
        $participant->delete();

        ActivityLog::log('delete', "Menghapus peserta: {$name}");

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->storeAs('imports', 'participants.xlsx');
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/' . $path));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $imported = 0;
            $skipped = 0;

            /*
             * Expected Excel format (based on user's file):
             * Row 1: Headers - No | Nama Lengkap Anak | No Whatsapp | Usia | Nama Orang Tua | Keterangan Lain
             * Row 2: Date info (e.g., "minggu 16 november 2025") - SKIP THIS
             * Row 3+: Actual data
             * 
             * Column mapping (0-indexed):
             * [0] = No (skip)
             * [1] = Nama Lengkap Anak
             * [2] = No Whatsapp
             * [3] = Usia
             * [4] = Nama Orang Tua
             * [5] = Keterangan Lain (category: umum, PERFORM, pribadi)
             */

            foreach ($rows as $index => $row) {
                // Skip header row (index 0)
                if ($index === 0) continue;
                
                // Skip if row[1] (nama) is empty - this catches date rows and empty rows
                $nama = trim($row[1] ?? '');
                if (empty($nama)) continue;
                
                // Skip if it looks like a date row (contains month names or "minggu")
                $lowerNama = strtolower($nama);
                if (str_contains($lowerNama, 'minggu') || 
                    str_contains($lowerNama, 'senin') || 
                    str_contains($lowerNama, 'selasa') ||
                    str_contains($lowerNama, 'november') ||
                    str_contains($lowerNama, 'desember') ||
                    str_contains($lowerNama, 'januari')) {
                    continue;
                }

                // Parse category from column F (index 5)
                $categoryRaw = strtolower(trim($row[5] ?? 'umum'));
                $category = 'umum';
                if (str_contains($categoryRaw, 'perform')) {
                    $category = 'perform';
                } elseif (str_contains($categoryRaw, 'pribadi')) {
                    $category = 'pribadi';
                }

                // Get phone number - KEEP AS IS, just remove non-numeric characters
                $phone = $row[2] ?? null;
                if ($phone) {
                    // Only keep digits, don't modify the number
                    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
                    // If empty after cleaning, set to null
                    if (empty($phone)) {
                        $phone = null;
                    }
                }

                // Get age
                $age = null;
                if (isset($row[3]) && is_numeric($row[3])) {
                    $age = (int)$row[3];
                }

                // Get guardian name
                $guardianName = trim($row[4] ?? '');

                try {
                    Participant::updateOrCreate(
                        ['name' => $nama],
                        [
                            'phone' => $phone,
                            'age' => $age,
                            'guardian_name' => $guardianName,
                            'category' => $category,
                        ]
                    );
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            ActivityLog::log('import', "Import peserta dari Excel: {$imported} berhasil, {$skipped} dilewati");

            return redirect()->route('participants.index')
                ->with('success', "Import berhasil! {$imported} peserta diimport, {$skipped} dilewati.");

        } catch (\Exception $e) {
            return redirect()->route('participants.index')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
