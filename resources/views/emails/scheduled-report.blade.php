<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan worklog</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Segoe UI',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#0f172a;padding:20px 24px;color:#ffffff;">
                            <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#93c5fd;">PT BORNEO PRIMA</p>
                            <h1 style="margin:6px 0 0;font-size:20px;">
                                @php($frequencyLabel = match ($frequency) {
                                    'daily' => 'Harian',
                                    'monthly' => 'Bulanan',
                                    default => 'Mingguan',
                                })
                                Laporan Worklog {{ $frequencyLabel }}
                            </h1>
                            <p style="margin:6px 0 0;font-size:12px;color:#cbd5e1;">
                                {{ \Carbon\Carbon::parse($filters['from'])->translatedFormat('d F Y') }} — {{ \Carbon\Carbon::parse($filters['to'])->translatedFormat('d F Y') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
                                <tr>
                                    <td style="width:33.33%;padding:0 4px;">
                                        <div style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;">
                                            <p style="margin:0;font-size:11px;color:#64748b;">Jumlah pekerjaan</p>
                                            <p style="margin:4px 0 0;font-size:22px;font-weight:700;">{{ number_format($summary['entries']) }}</p>
                                        </div>
                                    </td>
                                    <td style="width:33.33%;padding:0 4px;">
                                        <div style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;">
                                            <p style="margin:0;font-size:11px;color:#64748b;">Hari terisi</p>
                                            <p style="margin:4px 0 0;font-size:22px;font-weight:700;">{{ number_format($summary['days']) }}</p>
                                        </div>
                                    </td>
                                    <td style="width:33.33%;padding:0 4px;">
                                        <div style="border:1px solid #e2e8f0;border-radius:8px;padding:10px;">
                                            <p style="margin:0;font-size:11px;color:#64748b;">Anggota terlibat</p>
                                            <p style="margin:4px 0 0;font-size:22px;font-weight:700;">{{ number_format($summary['members']) }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <h2 style="margin:0 0 8px;font-size:15px;">Aktivitas per anggota</h2>
                            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:12px;margin-bottom:20px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="border-bottom:1px solid #e2e8f0;padding:6px;color:#64748b;">Anggota</th>
                                        <th align="right" style="border-bottom:1px solid #e2e8f0;padding:6px;color:#64748b;">Pekerjaan</th>
                                        <th align="right" style="border-bottom:1px solid #e2e8f0;padding:6px;color:#64748b;">Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perUser as $row)
                                        <tr>
                                            <td style="border-bottom:1px solid #f1f5f9;padding:6px;">{{ $row->name }}<br><span style="color:#64748b;font-size:11px;">{{ $row->position }}</span></td>
                                            <td align="right" style="border-bottom:1px solid #f1f5f9;padding:6px;font-weight:700;">{{ $row->entries }}</td>
                                            <td align="right" style="border-bottom:1px solid #f1f5f9;padding:6px;">{{ $row->days }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" style="padding:10px;color:#64748b;">Belum ada aktivitas pada periode ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <h2 style="margin:0 0 8px;font-size:15px;">Distribusi per project</h2>
                            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:12px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="border-bottom:1px solid #e2e8f0;padding:6px;color:#64748b;">Project</th>
                                        <th align="right" style="border-bottom:1px solid #e2e8f0;padding:6px;color:#64748b;">Pekerjaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perProject as $row)
                                        <tr>
                                            <td style="border-bottom:1px solid #f1f5f9;padding:6px;">{{ $row->code ?? 'Tanpa project' }}<br><span style="color:#64748b;font-size:11px;">{{ $row->name }}</span></td>
                                            <td align="right" style="border-bottom:1px solid #f1f5f9;padding:6px;font-weight:700;">{{ $row->entries }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" style="padding:10px;color:#64748b;">Belum ada distribusi project.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <p style="margin:20px 0 0;font-size:13px;line-height:1.6;color:#334155;">
                                Detail lengkap pekerjaan tersedia pada lampiran CSV. Laporan ini memakai jumlah pekerjaan dan hari terisi, bukan jam kerja.
                            </p>
                            <p style="margin:16px 0 0;">
                                <a href="{{ route('reports.index') }}" style="display:inline-block;background:#0066FF;color:#ffffff;text-decoration:none;font-weight:700;font-size:13px;padding:10px 16px;border-radius:8px;">Buka Laporan</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;line-height:1.6;color:#64748b;">Email ini dikirim otomatis oleh IT Worklog sesuai jadwal laporan pada Pengaturan.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
