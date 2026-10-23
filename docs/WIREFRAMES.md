# IT Worklog — Wireframes

Status: Planning v1.1  
Tanggal: 23 Oktober 2026  
Referensi: `BACKLOG.md`, `DATABASE.md`  
Sumber brand: `D:\web\eform-bp`, `D:\web\asset-tagging`

## 1. Prinsip Visual

- Mengikuti identitas PT Borneo Prima: biru resmi, navy, putih, dan tipografi Plus Jakarta Sans.
- Layout mengikuti shell `eform-bp`: sidebar navy, topbar putih, konten maksimal 1280px.
- Fokus pada keterbacaan dan kecepatan input, bukan dekorasi.
- Mode terang untuk MVP. Mode gelap masuk fase 2.
- Tidak ada JavaScript kustom; interaksi memakai Livewire dan Blade.

## 2. Brand Tokens

| Token | Nilai |
|---|---|
| Primary | `#0066FF` |
| Primary hover | `#0052CC` |
| Primary soft | `#EEF6FF` |
| Navy / sidebar | `#0F172A` |
| Background | `#F8FAFC` |
| Card | `#FFFFFF` |
| Border | `#E2E8F0` |
| Text | `#0F172A` |
| Muted | `#64748B` |
| Success | `#16A34A` |
| Warning | `#D97706` |
| Danger | `#DC2626` |
| Info | `#0284C7` |
| Font | Plus Jakarta Sans 400–800 |
| Radius card | 12px |
| Radius tombol | 8–12px |
| Radius login | pill |
| Shadow card | `0 1px 2px rgb(15 23 42 / 0.04)` |
| Hero | gradient `#0066FF` ke `#0F172A` |

Aset:

- `logo-bp.svg`
- `logo-bp.png`
- `login-side.jpg`
- Font dari Bunny Fonts.

Ambil aset dari `D:\web\eform-bp\public\images`. Adaptasi CSS `.bp-login`, `.ui-card`, `.ui-button-primary`, `.ui-badge`, `.app-sidebar`, dan `.app-topbar` dari `eform-bp/resources/css/app.css`.

## 3. Shell Layout

```text
Desktop
+------------------------------+------------------------------------------------+
| [logo] PT BORNEO PRIMA       | IT Worklog — PT Borneo Prima    23 Okt  [bell] |
|        IT WORKLOG            | Ringkasan aktivitas dan progres tim            |
+------------------------------+------------------------------------------------+
| RUANG KERJA                  | Beranda / Dasbor                 [+ Worklog]   |
|  > Dasbor                    |------------------------------------------------|
|  > Worklog                   |                                                |
|  > Aktivitas Tim             |                  KONTEN                        |
| KENDALI                      |             max-width 1280px                   |
|  > Projects                  |                                                |
|  > Tugas                     |                                                |
|  > Laporan                   |                                                |
| ADMINISTRASI                 |                                                |
|  > Users                     |                                                |
|  > Kategori                  |                                                |
|  > Pengaturan                |                                                |
|  > Audit Log                 |                                                |
|                              |                                                |
| v1.1 MVP                     |                                                |
+------------------------------+------------------------------------------------+

Mobile
+----------------------------------+
| [=] [logo] IT Worklog     [bell] |
+----------------------------------+
|                                  |
|            KONTEN                |
|                                  |
+----------------------------------+
```

Ketentuan:

- Sidebar 256px, background navy, label grup uppercase 10px.
- Item aktif memakai pill biru `#0066FF` dan teks putih.
- Topbar 64px, sticky, background putih, border bawah.
- Mobile memakai drawer dari kiri, bukan bottom navigation.
- Breadcrumb dan judul halaman berada di page header.

## 4. Login

```text
+-------------------------------+-------------------------------+
| [logo]                        | FOTO / VEIL NAVY              |
| PT BORNEO PRIMA • IT WORKLOG  |                               |
|                               | "Setiap pekerjaan tercatat,   |
| Selamat Datang                |  setiap progres terlihat."    |
|                               |                               |
| Alamat email                  | [Kartu fitur 1]               |
| [___________________________] | [Kartu fitur 2]               |
| Kata sandi                    | [Kartu fitur 3]               |
| [___________________________] |                               |
| [ ] Ingat saya   Lupa sandi?  |                               |
| [    Masuk ke Dashboard ->  ] | borneoprima.com ↗             |
|                               |                               |
| Akses internal PT Borneo Prima|                               |
+-------------------------------+-------------------------------+
```

Catatan:

- Layout split 2 kolom di desktop, 1 kolom di mobile.
- Panel kanan memakai foto dan veil navy.
- Badge `PT BORNEO PRIMA • IT WORKLOG` berwarna biru muda.
- Tombol utama pill biru.
- Error tampil di bawah field terkait.

## 5. Dashboard

```text
+--------------------------------------------------------------+
| HERO GRADIENT #0066FF -> #0F172A                             |
| IT WORKLOG / COMMAND CENTER                                  |
| Selamat datang kembali, Andika.                              |
| Pantau aktivitas dan progres tim dari satu ruang kerja.      |
|                                                              |
| [ Isi Worklog Hari Ini ]                                     |
+--------------------------------------------------------------+
| [Aktivitas hari ini 3] [Laporan tim 4/7]                     |
| [Task overdue 2]       [Project aktif 5]                     |
|  border kiri biru / amber / rose / emerald                   |
+--------------------------------------------------------------+
| Aktivitas tim hari ini              Tugas mendatang          |
| +--------------------------------+  +-----------------------+ |
| | (A) Andika       3 item [Lihat]|  | Fix backup  23 Okt   | |
| | (B) Budi         2 item [Lihat]|  | Review akses 24 Okt  | |
| | (C) Citra   Belum isi [Ingatkan]| | Migrasi DB  25 Okt   | |
| +--------------------------------+  +-----------------------+ |
+--------------------------------------------------------------+
| Health project                                               |
| PRJ-1 Migrasi Server    [======----] 60%   Due 30 Okt         |
| PRJ-2 Support Internal  [===-------] 35%   Due 15 Nov         |
+--------------------------------------------------------------+
```

Perbedaan peran:

- Member: kartu "Laporan tim" tetap tampil, tombol "Ingatkan" disembunyikan.
- Manager: melihat semua anggota dan tombol "Ingatkan".
- Admin: sama seperti manager.

Tidak ada kartu jam, total menit, atau target 8 jam.

## 6. Worklog Hari Ini

```text
+--------------------------------------------------------------+
| Worklog / Hari Ini                 [< Jumat, 23 Okt 2026 >]  |
| Status: Belum diisi                          [Simpan Worklog] |
|                                                              |
| Apa saja yang Anda kerjakan hari ini? *                      |
| +----------------------------------------------------------+ |
| | 1  [Project v]  [Task v]  [Kategori v]                   | |
| |    [Deskripsi pekerjaan...]                              | |
| |    [Pilih lampiran]                            [Hapus]   | |
| +----------------------------------------------------------+ |
| + Tambah pekerjaan                                           |
|                                                              |
| Catatan / blocker (opsional)                                 |
| [__________________________________________________________] |
|                                                              |
| 3 pekerjaan dicatat                                          |
+--------------------------------------------------------------+
```

Ketentuan:

- Satu baris default saat halaman dibuka.
- Project, task, dan kategori opsional.
- Deskripsi wajib.
- Tidak ada input durasi, jam mulai, atau jam selesai.
- Lampiran maksimal 3 per pekerjaan.
- Tombol simpan nonaktif jika belum ada pekerjaan dengan deskripsi.
- Mobile: setiap pekerjaan menjadi kartu, tombol simpan sticky di bawah.
- Status berubah menjadi "Sudah diisi" setelah minimal satu pekerjaan tersimpan.

## 7. Detail Worklog

```text
+--------------------------------------------------------------+
| < Kembali   Worklog Andika — 23 Oktober 2026   [Edit] [Hapus]|
| 3 pekerjaan dicatat · diedit 2x                              |
|                                                              |
| 1. PRJ-1 / Migrasi DB / Development                          |
|    Migrasi database staging ke server baru.                  |
|    [IMG_01] [IMG_02]                                         |
|                                                              |
| 2. PRJ-2 / — / Support                                       |
|    Perbaikan printer dan pengecekan jaringan lantai 2.       |
|                                                              |
| 3. — / — / Meeting                                           |
|    Meeting koordinasi vendor.                                |
|                                                              |
| Catatan: Menunggu akses VPN.                                 |
|                                                              |
| Riwayat perubahan                                            |
| 14:20  Andika  menambah pekerjaan ke-3                       |
| 13:05  Andika  mengubah deskripsi pekerjaan ke-2             |
+--------------------------------------------------------------+
```

Jika laporan soft-deleted, tampilkan banner "Laporan dihapus" dan tombol "Pulihkan" hanya untuk admin.

## 8. Feed Tim

```text
+--------------------------------------------------------------+
| Aktivitas Tim                                                |
| [Hari ini] [Minggu ini] [Rentang]   [Anggota v] [Project v]  |
|                                                              |
| +----------------------------------------------------------+ |
| | (A) Andika Kuswidyarto          23 Okt 2026    [3 item]   | |
| | Migrasi server, support printer, meeting vendor           | |
| | [PRJ-1 Migrasi] [Support] [Meeting]          2 lampiran   | |
| |                                             [Lihat detail]| |
| +----------------------------------------------------------+ |
| +----------------------------------------------------------+ |
| | (B) Budi Santoso                23 Okt 2026    [2 item]   | |
| | Pengecekan backup dan update akses.                       | |
| | [Support] [Maintenance]                                   | |
| +----------------------------------------------------------+ |
|                                            [1] [2] [3] [>]   |
+--------------------------------------------------------------+
```

Ketentuan:

- Detail terbuka untuk semua user yang login.
- Yang ditonjolkan: jumlah pekerjaan, ringkasan deskripsi, chip project/kategori, jumlah lampiran.
- Tidak ada durasi atau total jam.
- Filter memakai segmen tanggal dan dropdown anggota/project.

## 9. Projects

```text
Daftar
+--------------------------------------------------------------+
| Projects                                       [+ Project]   |
| [Cari nama/kode] [Status v] [Owner v]                        |
|                                                              |
| +----------------------------------------------------------+ |
| | PRJ-1  Migrasi Server            [Active]   60%  30 Okt   | |
| | Owner Andika · 4 anggota · 12 task · 3 milestone          | |
| +----------------------------------------------------------+ |
| | PRJ-2  Support Internal          [On hold]  30%  —        | |
| +----------------------------------------------------------+ |
+--------------------------------------------------------------+

Detail
+--------------------------------------------------------------+
| PRJ-1  Migrasi Server                            [Edit]       |
| [Active] Owner Andika · 1 Okt – 30 Nov 2026 · 4 anggota      |
| Progress [==========------------] 60%                        |
|                                                              |
| [Ringkasan] [Milestone] [Task] [Worklog]                     |
|                                                              |
| Deskripsi project ...                                        |
| [Task 12] [Selesai 7] [Milestone 3] [Aktivitas 24]           |
| Deadline terdekat: Cutover 30 Okt                            |
| Anggota: (A) (B) (C) (D)                                     |
+--------------------------------------------------------------+
```

Tab Worklog menampilkan daftar aktivitas project tanpa durasi, beserta kontributor dan tanggal.

## 10. Tasks

```text
+--------------------------------------------------------------+
| Tugas Saya                 [Status v] [Prioritas v] [Cari]   |
|                                                              |
| Todo (3)           In Progress (2)    Blocked (1)            |
| +---------------+  +---------------+  +---------------+      |
| | Fix backup    |  | Migrasi DB    |  | VPN akses     |      |
| | PRJ-1         |  | PRJ-1         |  | PRJ-2         |      |
| | [High] 23 Okt |  | [Urgent]      |  | [Normal]      |      |
| +---------------+  +---------------+  +---------------+      |
+--------------------------------------------------------------+
```

Ketentuan:

- Manager mendapat tab "Semua Tugas".
- Kartu memakai border slate, badge prioritas pill, dan status diubah lewat dropdown.
- Tanpa drag-and-drop.
- Mobile: daftar per status, bukan kolom horizontal.
- Tidak ada estimasi menit pada form task.

## 11. Reports

```text
+--------------------------------------------------------------+
| Laporan                                                      |
| [Minggu ini v] [User v] [Project v] [Kategori v]             |
|                                                              |
| [Hari terisi 18/24] [Aktivitas 76] [Kepatuhan 75%]           |
| [Project aktif 5]                                            |
|                                                              |
| [Per user] [Per project]                    [CSV] [Print]    |
|                                                              |
| User   | Hari  | Aktivitas | Project | Task selesai          |
| Andika | 5/6   | 24        | 3       | 7                     |
| Budi   | 6/6   | 31        | 4       | 9                     |
+--------------------------------------------------------------+
```

Tab per project:

```text
Project | Aktivitas | Kontributor | Task selesai | Progress
PRJ-1   | 24        | 4           | 7/12         | 60%
```

Ketentuan:

- Keputusan berdasarkan jumlah hari terisi dan jumlah pekerjaan.
- Tidak ada total jam atau workload berbasis jam.
- Print view menyembunyikan sidebar dan filter.
- CSV mengikuti filter aktif.

## 12. Admin

```text
+--------------------------------------------------------------+
| Admin / Users                                  [+ User]      |
| [Users] [Categories] [Settings] [Audit Log]                  |
|                                                              |
| [Cari] [Role v] [Status v]                                   |
| Nama    | Email             | Role    | Status  | Last login |
| Andika  | andika@...        | Admin   | Aktif   | 23 Okt     |
| Budi    | budi@...          | Member  | Aktif   | 22 Okt     |
| Citra   | citra@...         | Manager | Nonaktif| —          |
+--------------------------------------------------------------+
```

Audit log:

```text
+--------------------------------------------------------------+
| [User v] [Event v] [Tanggal]                                 |
| 23 Okt 14:20  Andika  updated  Worklog #12    [Lihat diff]   |
| 23 Okt 13:05  Andika  created  Task #44                      |
+--------------------------------------------------------------+
```

Diff ditampilkan sebagai modal dua kolom: nilai lama dan nilai baru.

## 13. Pemetaan Komponen

| Layar | Route | Komponen Livewire |
|---|---|---|
| Login | `/login` | Blade + Breeze |
| Dashboard | `/dashboard` | `Dashboard\Index` |
| Worklog | `/worklogs/{date?}` | `Worklog\Today`, `Worklog\ItemRow` |
| Detail worklog | `/worklogs/{report}` | `Worklog\Show`, `Worklog\History` |
| Feed | `/feed` | `Worklog\Feed` |
| Projects | `/projects` | `Projects\Index` |
| Detail project | `/projects/{project}` | `Projects\Show`, `Projects\Milestones`, `Projects\Tasks` |
| Tasks | `/tasks` | `Tasks\Index` |
| Reports | `/reports` | `Reports\Index`, `Reports\Filters` |
| Admin users | `/admin/users` | `Admin\Users` |
| Admin kategori | `/admin/categories` | `Admin\Categories` |
| Admin pengaturan | `/admin/settings` | `Admin\Settings` |
| Admin audit | `/admin/audit` | `Admin\Audit` |

Komponen bersama:

- `components/status-badge`
- `components/priority-badge`
- `components/empty-state`
- `components/progress-bar`
- `components/page-header`
- `components/confirm-dialog`
- `components/attachment-list`

## 14. Aturan Interaksi

- Semua aksi memakai Livewire; tanpa JavaScript kustom.
- Modal dan konfirmasi hapus dirender dari server.
- Pencarian dan filter memakai `wire:model.live.debounce.300ms`.
- Pagination memakai Livewire.
- Perubahan status task lewat dropdown atau tombol, bukan drag-and-drop.
- Setiap form memiliki label, pesan error inline, dan state loading.
- Empty state wajib untuk daftar kosong dan hasil filter kosong.
- Flash message muncul setelah simpan, edit, dan hapus.
- Tombol simpan worklog nonaktif jika belum ada pekerjaan valid.

## 15. Responsive

- Kurang dari 1024px: sidebar menjadi drawer.
- Worklog: baris pekerjaan menjadi kartu; tombol simpan sticky.
- Task board: berubah menjadi daftar per status.
- Tabel laporan: scroll horizontal atau kartu ringkas.
- Target sentuh minimal 44px.
- Topbar mobile menampilkan hamburger, logo, nama modul, notifikasi, dan avatar.

## 16. Aksesibilitas

- Semua input memiliki label eksplisit.
- Error terhubung ke field dengan `aria-describedby`.
- Fokus keyboard terlihat jelas.
- `aria-current="page"` pada menu aktif.
- Kontras teks minimal WCAG AA.
- Tombol ikon memiliki `aria-label`.
- Dialog konfirmasi dapat ditutup dengan keyboard.
- Skip link menuju konten utama.

## 17. Catatan Implementasi

- Gunakan CSS variables dari `eform-bp` agar warna konsisten.
- Tailwind theme: `primary`, `navy`, `border`, `muted`, radius.
- Font Plus Jakarta Sans dimuat dari Bunny Fonts.
- Logo ditempatkan di sidebar, login, dan favicon.
- Tidak ada dependensi runtime Node.js; aset dapat dibangun dengan Tailwind standalone CLI.
- Mode gelap belum diimplementasikan; struktur class dapat disiapkan tanpa mengaktifkannya.
- Worklog tidak menampilkan input durasi atau total jam di semua layar.
