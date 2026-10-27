<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Worklog {{ $filters['from'] }} — {{ $filters['to'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #0f172a; font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; }
        h1 { margin: 0; font-size: 20px; }
        h2 { margin: 0 0 8px; font-size: 14px; }
        p { margin: 4px 0; }
        .muted { color: #64748b; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .summary { display: flex; gap: 10px; margin-bottom: 18px; }
        .summary div { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; }
        .summary strong { display: block; font-size: 20px; }
        .grid { display: flex; gap: 16px; margin-bottom: 18px; }
        .grid section { flex: 1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; }
        @page { size: A4; margin: 12mm; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <p class="muted">PT BORNEO PRIMA · IT WORKLOG</p>
            <h1>Laporan Aktivitas Worklog</h1>
            <p class="muted">Periode {{ \Carbon\Carbon::parse($filters['from'])->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($filters['to'])->translatedFormat('d F Y') }}</p>
        </div>
        <div style="text-align: right">
            <p class="muted">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</p>
            <button class="no-print" onclick="window.print()" style="margin-top: 8px; padding: 6px 10px; cursor: pointer;">Print / Save PDF</button>
        </div>
    </div>

    <div class="summary">
        <div><span class="muted">Jumlah pekerjaan</span><strong>{{ number_format($summary['entries']) }}</strong></div>
        <div><span class="muted">Hari terisi</span><strong>{{ number_format($summary['days']) }}</strong></div>
        <div><span class="muted">Anggota terlibat</span><strong>{{ number_format($summary['members']) }}</strong></div>
        <div><span class="muted">Rata-rata per hari</span><strong>{{ $summary['days'] > 0 ? number_format($summary['entries'] / $summary['days'], 1) : '0' }}</strong></div>
    </div>

    <div class="grid">
        <section>
            <h2>Aktivitas per anggota</h2>
            <table>
                <thead><tr><th>Anggota</th><th>Pekerjaan</th><th>Hari</th></tr></thead>
                <tbody>
                    @forelse ($perUser as $row)
                        <tr><td>{{ $row->name }}<br><span class="muted">{{ $row->position }}</span></td><td>{{ $row->entries }}</td><td>{{ $row->days }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section>
            <h2>Distribusi per project</h2>
            <table>
                <thead><tr><th>Project</th><th>Pekerjaan</th></tr></thead>
                <tbody>
                    @forelse ($perProject as $row)
                        <tr><td>{{ $row->code ?? 'Tanpa project' }}<br><span class="muted">{{ $row->name }}</span></td><td>{{ $row->entries }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <h2>Detail pekerjaan</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Anggota</th><th>Project</th><th>Kategori</th><th>Pekerjaan</th><th>Lampiran</th></tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ $entry->dailyReport?->report_date?->format('d/m/Y') }}</td>
                    <td>{{ $entry->dailyReport?->user?->name }}</td>
                    <td>{{ $entry->project?->code ?? '—' }}</td>
                    <td>{{ $entry->category?->name ?? '—' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->attachments_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Tidak ada pekerjaan pada filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini memakai jumlah pekerjaan dan hari terisi, bukan jam kerja. Dokumen dibuat otomatis oleh IT Worklog.
    </div>
</body>
</html>
