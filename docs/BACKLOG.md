# IT Worklog — Product Backlog

Status: Planning v1.1  
Tanggal: 23 Oktober 2026  
Target: MVP siap produksi dalam 6 sprint mingguan

Dokumen terkait: `DATABASE.md`, `WIREFRAMES.md`

## 1. Ringkasan

IT Worklog adalah aplikasi internal untuk mencatat daftar pekerjaan harian tim IT, memantau aktivitas dan beban kerja berdasarkan jumlah pekerjaan, serta melacak progres project jangka panjang dalam satu tempat. Pengisian worklog harus selesai dalam waktu kurang dari 60 detik.

Fokus MVP adalah visibilitas aktivitas, bukan pencatatan waktu. Durasi, jam mulai/selesai, timesheet, dan target jam tidak termasuk fase pertama.

## 2. Keputusan Produk

| Topik | Keputusan |
|---|---|
| Platform | Web, Laravel + Blade + Livewire 3 |
| Runtime | PHP saja, tanpa Node.js |
| Database | MySQL 8 |
| Hosting | Shared hosting cPanel |
| Login | Lokal, email + password |
| Jumlah user MVP | Kurang dari 10 |
| Approval worklog | Tidak ada |
| Worklog | Daftar pekerjaan harian; tanpa durasi atau jam |
| Isi worklog | Minimal satu pekerjaan; project, task, dan kategori opsional |
| Satuan laporan | Jumlah pekerjaan dan hari terisi, bukan jam |
| Hari kerja | Senin–Sabtu |
| Visibilitas | Detail worklog terbuka untuk semua anggota yang login |
| Edit worklog | Bebas, dengan audit before/after dan soft delete |
| Lampiran | Masuk MVP, disimpan privat |
| Import data lama | Tidak ada |
| Reminder | Email pukul 17:00, Senin–Sabtu |
| Branding | PT Borneo Prima Official Blue, Plus Jakarta Sans |
| Mode gelap | Fase 2 |

## 3. Peran

| Peran | Hak akses |
|---|---|
| Member | Isi/edit worklog sendiri, lihat project dan task yang diikuti, lihat detail worklog tim |
| Manager | Hak member, kelola project/milestone/task, lihat dan laporkan data tim |
| Admin | Kelola user, kategori, pengaturan, audit log, seluruh data |

## 4. Aturan Bisnis

- Satu laporan harian per user per tanggal.
- Laporan dianggap terisi jika memiliki minimal satu pekerjaan aktif.
- Setiap pekerjaan wajib memiliki deskripsi.
- Project, task, dan kategori bersifat opsional. Jika tersedia, kategori `Umum` dipilih otomatis.
- Tidak ada durasi, jam mulai/selesai, total jam, atau target jam di MVP.
- Tanggal masa depan ditolak.
- Menghapus pekerjaan terakhir otomatis melakukan soft delete pada laporan harian.
- Edit worklog bebas; setiap perubahan dicatat di audit log.
- Hapus worklog menggunakan soft delete; admin dapat memulihkan.
- Project status: `planning`, `active`, `on_hold`, `completed`, `cancelled`.
- Task status: `todo`, `in_progress`, `blocked`, `done`.
- Prioritas task: `low`, `normal`, `high`, `urgent`.
- Progress project dihitung dari task `done` dibagi total task.
- Kepatuhan worklog dihitung dari hari kerja yang memiliki laporan.
- Laporan menampilkan jumlah hari terisi dan jumlah pekerjaan, bukan total jam.
- Semua akses data melewati Policy untuk mencegah IDOR.
- Lampiran hanya dapat diakses user terautentikasi melalui route ber-Policy.
- Lampiran maksimal 3 file per pekerjaan, maksimal 5 MB per file.

## 5. Non-Goals MVP

- Time tracking, timesheet, timer start/stop, target jam
- Perhitungan payroll atau billing berbasis jam
- Approval berlapis
- Integrasi Git, Jira, Trello, payroll
- Portal client
- Aplikasi mobile native atau PWA
- Kalender hari libur nasional
- Chart interaktif
- Notifikasi WhatsApp/Telegram
- Mode gelap

## 6. Brand dan UI

Brand mengikuti aplikasi PT Borneo Prima yang sudah ada, terutama `eform-bp` dan `asset-tagging`.

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
| Font | Plus Jakarta Sans 400–800 |
| Radius | Card 12px, tombol 8–12px, login pill |

Ketentuan UI:

- Sidebar navy 256px dengan logo `logo-bp.svg` dan label `PT BORNEO PRIMA` + `IT WORKLOG`.
- Topbar putih 64px, judul modul dan subjudul.
- Kartu memakai border slate dan shadow tipis.
- Hero dashboard memakai gradient `#0066FF` ke `#0F172A`.
- Badge status berbentuk pill dengan ring.
- Login memakai layout split dan CSS `.bp-login` dari `eform-bp`.
- Detail wireframe ada di `WIREFRAMES.md`.

## 7. Definition of Ready

- Acceptance criteria jelas.
- Dependensi sprint sebelumnya selesai.
- Desain atau wireframe halaman tersedia.
- Data contoh dan aturan validasi disepakati.
- Aset brand tersedia di folder `public/images`.

## 8. Definition of Done

- Acceptance criteria terpenuhi.
- Feature test untuk alur utama dan Policy test untuk akses.
- Lulus `./vendor/bin/pint --test`.
- Tidak ada N+1 pada halaman list.
- Responsive di layar mobile.
- Mengikuti brand PT Borneo Prima.
- Berhasil di-deploy ke staging.

## 9. Asumsi Estimasi

- 1 developer.
- 6 hari kerja per minggu.
- Sekitar 6 jam efektif per hari.
- Estimasi dalam hari ideal dan sudah mempertimbangkan buffer kecil.
- Jika kapasitas berbeda, timeline menyesuaikan tanpa mengubah urutan prioritas.

## 10. Peta Sprint

| Sprint | Fokus | Estimasi |
|---|---|---|
| 1 | Fondasi, auth, role, master data, staging | 6 hari |
| 2 | Project, anggota, milestone | 6 hari |
| 3 | Task dan awal worklog | 6 hari |
| 4 | Worklog, lampiran, feed | 6 hari |
| 5 | Dashboard, laporan, reminder | 6 hari |
| 6 | Audit, hardening, UAT, go-live | 6 hari |

## 11. Backlog per Epic

### E1 — Auth dan Akses

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-01 | Login dan logout | Email dan password; user nonaktif ditolak; rate limit 5 percobaan per menit; redirect ke dashboard | Must | 0,5 | 1 |
| US-02 | Lupa password | Email reset terkirim; tautan kedaluwarsa 60 menit; password baru tervalidasi | Must | 0,5 | 1 |
| US-03 | Admin kelola user | CRUD user; email unik; tidak bisa menonaktifkan diri sendiri; role dapat diubah | Must | 1 | 1 |
| US-04 | Role dan Policy | Tiga role; member ditolak di route admin; manager hanya mengakses data tim | Must | 1,5 | 1 |
| US-05 | Profil | Ubah nama, jabatan, dan password; perubahan email hanya melalui admin | Should | 0,5 | 1 |

### E2 — Master Data

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-06 | Kategori kerja | CRUD nama dan warna; kategori nonaktif tidak muncul di form baru; tersedia kategori `Umum` | Must | 0,75 | 1 |
| US-07 | Pengaturan kerja | Timezone Asia/Jakarta; hari kerja Senin–Sabtu; reminder 17:00; tanpa target jam | Must | 0,75 | 1 |

### E3 — Project

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-08 | CRUD project | Kode unik; owner; tanggal mulai dan selesai; status; due date tidak lebih awal dari start date | Must | 1 | 2 |
| US-09 | Anggota project | Tambah dan hapus anggota; non-anggota mendapat 403; owner harus ditransfer sebelum keluar | Must | 0,75 | 2 |
| US-10 | Milestone | CRUD; deadline; status; overdue dihitung otomatis | Must | 1 | 2 |
| US-11 | Overview project | Progress dari task; jumlah milestone; jumlah aktivitas; deadline terdekat; daftar anggota | Must | 0,75 | 2 |
| US-12 | Daftar dan filter project | Filter status dan owner; pencarian; pagination | Should | 0,5 | 2 |

### E4 — Task

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-13 | CRUD task | Project wajib; milestone opsional; assignee; prioritas; deadline; tanpa estimasi menit | Must | 1 | 3 |
| US-14 | Workflow status | todo ke in_progress ke blocked atau done; completed_at terisi; hanya assignee atau manager | Must | 0,75 | 3 |
| US-15 | Tugas Saya | Hanya task milik sendiri; urut deadline; filter status dan overdue | Must | 0,75 | 3 |
| US-16 | Board sederhana | Kolom per status; responsive; tanpa drag-and-drop | Should | 0,5 | 3 |

### E5 — Worklog

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-17 | Worklog harian | Satu laporan per user per tanggal; minimal satu pekerjaan; tanggal masa depan ditolak; catatan opsional | Must | 0,75 | 3 |
| US-18 | Daftar pekerjaan | Tambah, ubah, hapus pekerjaan; project/task/kategori opsional; deskripsi wajib; tanpa durasi | Must | 0,75 | 3 |
| US-19 | Quick add Hari Ini | Satu layar; satu baris default; prefill tanggal hari ini; submit kurang dari 60 detik; nyaman di HP | Must | 0,75 | 4 |
| US-20 | Edit dan hapus dengan audit | Edit bebas; soft delete; audit old/new; admin dapat restore; halaman riwayat perubahan | Must | 1,5 | 4 |
| US-21 | Lampiran | Disk privat; maksimal 3 file per pekerjaan; JPG, PNG, WEBP, PDF maksimal 5 MB; akses melalui route ber-Policy | Must | 1,5 | 4 |
| US-22 | Feed tim | Kronologis; jumlah pekerjaan; deskripsi ringkas; chip project/kategori; filter tanggal dan anggota; pagination | Must | 0,75 | 4 |
| US-23 | Riwayat worklog | Filter tanggal, project, dan anggota; hanya menampilkan data yang boleh diakses | Should | 0,25 | 4 |

### E6 — Dashboard dan Laporan

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-24 | Dashboard pribadi | Status hari ini; jumlah pekerjaan minggu ini; hari terisi; task terbuka; worklog terbaru | Must | 0,75 | 5 |
| US-25 | Dashboard manager | Siapa belum isi hari ini; jumlah pekerjaan per anggota; task overdue; health project | Must | 1 | 5 |
| US-26 | Laporan terfilter | Filter tanggal, user, project, dan kategori; jumlah hari terisi dan jumlah pekerjaan; hak akses sesuai role | Must | 1 | 5 |
| US-27 | Export CSV | Mengikuti filter; streamed download; nama file menyertakan tanggal | Must | 0,5 | 5 |
| US-28 | Print view | Halaman bersih untuk cetak atau simpan PDF dari browser | Should | 0,5 | 5 |
| US-29 | Aktivitas per anggota | Jumlah pekerjaan per anggota; hari terisi; project terlibat; tanpa perhitungan jam | Should | 0,5 | 5 |
| US-30 | Progres project | Task selesai dibanding total; milestone; overdue; aktivitas terbaru | Should | 0,5 | 5 |

### E7 — Reminder

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-31 | Email pengingat | Senin–Sabtu pukul 17:00; hanya user tanpa laporan; sekali per hari; tautan ke form; status kirim dicatat | Must | 1 | 5 |

### E8 — Audit dan Admin

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-32 | Audit log viewer | Filter user, event, dan tanggal; tampilkan old/new; hanya admin | Must | 1 | 6 |
| US-33 | Restore data | Admin dapat memulihkan worklog, pekerjaan, atau lampiran yang soft-deleted | Should | 0,5 | 6 |

### E9 — Operasional

| ID | User story | Acceptance criteria | Prioritas | Estimasi | Sprint |
|---|---|---|---|---|---|
| US-34 | Deploy produksi | Docroot public; env produksi; migrate; cron scheduler dan queue; HTTPS | Must | 1 | 6 |
| US-35 | Backup dan restore | Backup harian database dan storage; prosedur restore diuji | Must | 0,75 | 6 |
| US-36 | Security hardening | APP_DEBUG=false; secure headers; session aman; rate limit | Must | 0,5 | 6 |
| US-37 | Monitoring | Uptime check dan review error log | Could | 0,25 | 6 |

## 12. Cut Line MVP

Wajib: seluruh item berprioritas Must, sekitar 24 hari.

Jika waktu tidak cukup, item berikut ditunda tanpa mengganggu alur utama:

- US-05 Profil
- US-12 Filter project
- US-16 Board sederhana
- US-23 Riwayat worklog lanjutan
- US-28 Print view
- US-29 Aktivitas per anggota
- US-30 Progres project
- US-33 Restore data
- US-37 Monitoring

## 13. Kebutuhan Infrastruktur

- PHP 8.2 atau lebih baru.
- MySQL 8.
- Cron setiap menit untuk `schedule:run` dan queue worker.
- SMTP cPanel dengan SPF dan DKIM yang benar.
- Docroot domain diarahkan ke folder `public`.
- Batas upload minimal 5 MB.
- Backup harian database dan folder storage.

## 14. Aset Brand

Salin dari `D:\web\eform-bp\public\images`:

- `logo-bp.svg`
- `logo-bp.png`
- `login-side.jpg`

Font Plus Jakarta Sans dimuat dari Bunny Fonts. CSS login dapat mengadaptasi `.bp-login` dari `eform-bp/resources/css/app.css`.

## 15. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Adopsi rendah | Input kurang dari 60 detik, satu baris default, reminder harian, dashboard manager |
| Data worklog tidak konsisten | Deskripsi wajib, minimal satu pekerjaan, audit perubahan |
| Persepsi worklog tidak mengukur waktu | Jelaskan fokus MVP pada visibilitas aktivitas; time tracking tersedia sebagai fase 2 |
| Edit bebas menurunkan akuntabilitas | Audit before/after yang dapat dilihat manager |
| Transparansi terbuka menimbulkan ketidaknyamanan | Sosialisasi kebijakan sebelum go-live |
| Lampiran memuat data sensitif | Disk privat, akses ber-Policy, validasi tipe dan ukuran |
| Keterbatasan shared hosting | Deploy staging di sprint 1, queue dan scheduler melalui cron |
| Email masuk spam | Konfigurasi SPF, DKIM, DMARC, dan uji pengiriman awal |
| Estimasi mepet | Cut line Should dan Could sudah disiapkan |

## 16. Pertanyaan Terbuka

- Alamat email pengirim dan kredensial SMTP.
- Daftar anggota dan admin pertama.
- Apakah hari libur nasional perlu dikecualikan dari reminder.
- Retensi lampiran dan audit log.
- Apakah manager boleh mengedit worklog anggota.
- Format laporan yang paling dibutuhkan Kepala IT.
- Apakah kategori sebaiknya wajib atau tetap opsional.
- Apakah mode gelap diperlukan pada fase berikutnya.

## 17. Rencana Verifikasi

- `php artisan migrate:status` setelah migration.
- `php artisan route:list` setelah penambahan route.
- `php artisan test` sebelum setiap akhir sprint.
- `./vendor/bin/pint --test` sebelum merge.
- UAT bersama manager dan satu anggota tim pada sprint 6.
- Restore backup diuji pada environment staging.
- Verifikasi bahwa worklog tidak meminta durasi atau total jam.
- Verifikasi laporan memakai jumlah pekerjaan dan hari terisi.
