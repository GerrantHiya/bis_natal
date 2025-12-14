<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Pengelompokan Bis - Natal SM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .actions {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn-print {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 5px;
        }
        
        .btn-print:hover {
            background: #4338ca;
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
        }
        
        .bus-section {
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .bus-header {
            padding: 15px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bus-header.morning {
            background: #10b981;
        }
        
        .bus-header.regular {
            background: #3b82f6;
        }
        
        .bus-header h2 {
            font-size: 18px;
        }
        
        .bus-header .count {
            font-size: 20px;
            font-weight: bold;
        }
        
        .passenger-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .passenger-table th,
        .passenger-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .passenger-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .passenger-table tr:hover {
            background: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }
        
        .badge-perform { background: #10b981; }
        .badge-umum { background: #3b82f6; }
        .badge-guardian { background: #8b5cf6; }
        
        .empty {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .bus-section {
                box-shadow: none;
                border: 1px solid #e5e7eb;
            }
            
            .header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .bus-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎄 Pengelompokan Bis Natal Sekolah Minggu</h1>
        <p>Daftar penumpang per bis</p>
    </div>
    
    <div class="actions">
        <a href="{{ route('assignments.index') }}" class="btn-back">← Kembali</a>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Print</button>
    </div>
    
    @foreach($buses as $bus)
        <div class="bus-section">
            <div class="bus-header {{ $bus->departure_time === '06:00' ? 'morning' : 'regular' }}">
                <h2>{{ $bus->name }} - {{ $bus->departure_time }} {{ $bus->departure_time === '06:00' ? '(PERFORM)' : '(UMUM)' }}</h2>
                <span class="count">{{ $bus->assignments->count() }} / {{ $bus->capacity }} Orang</span>
            </div>
            
            @if($bus->assignments->isEmpty())
                <div class="empty">Belum ada penumpang</div>
            @else
                <table class="passenger-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama</th>
                            <th width="100">Tipe</th>
                            <th width="100">Kategori</th>
                            <th>Pendamping</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bus->assignments as $index => $assignment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $assignment->display_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $assignment->is_guardian ? 'badge-guardian' : 'badge-' . ($assignment->participant->category ?? 'umum') }}">
                                        {{ $assignment->is_guardian ? 'Pendamping' : 'Anak' }}
                                    </span>
                                </td>
                                <td>
                                    @if(!$assignment->is_guardian && $assignment->participant)
                                        <span class="badge badge-{{ $assignment->participant->category }}">
                                            {{ strtoupper($assignment->participant->category) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!$assignment->is_guardian && $assignment->participant)
                                        {{ $assignment->participant->guardian_name ?: '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</body>
</html>
