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

            foreach ($rows as $index => $row) {
                if ($index === 0 || empty($row[1])) continue;

                $categoryRaw = strtolower(trim($row[5] ?? 'umum'));
                $category = 'umum';
                if (str_contains($categoryRaw, 'perform')) {
                    $category = 'perform';
                } elseif (str_contains($categoryRaw, 'pribadi')) {
                    $category = 'pribadi';
                }

                $phone = $row[2] ?? null;
                if ($phone) {
                    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
                    if (strlen($phone) > 10) {
                        $phone = '0' . substr($phone, -10);
                    }
                }

                try {
                    Participant::updateOrCreate(
                        ['name' => trim($row[1])],
                        [
                            'phone' => $phone,
                            'age' => is_numeric($row[3]) ? (int)$row[3] : null,
                            'guardian_name' => trim($row[4] ?? ''),
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
