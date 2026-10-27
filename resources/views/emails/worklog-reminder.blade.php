<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengingat worklog</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Segoe UI',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#0f172a;padding:20px 24px;color:#ffffff;">
                            <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#93c5fd;">PT BORNEO PRIMA</p>
                            <h1 style="margin:6px 0 0;font-size:20px;">IT Worklog</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:15px;">Halo, <strong>{{ $user->name }}</strong>.</p>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#334155;">
                                Worklog Anda untuk hari ini belum tercatat. Luangkan waktu sebentar untuk menuliskan pekerjaan yang sudah Anda kerjakan.
                            </p>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#334155;">
                                Pengisian worklog memakai daftar pekerjaan harian, tanpa durasi atau jam kerja.
                            </p>
                            <a href="{{ route('worklogs.index') }}" style="display:inline-block;background:#0066FF;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:11px 18px;border-radius:8px;">
                                Isi Worklog Sekarang
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;line-height:1.6;color:#64748b;">
                                Email ini dikirim otomatis oleh IT Worklog pada hari kerja jika worklog belum diisi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
