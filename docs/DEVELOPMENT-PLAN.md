# IT Worklog — Rencana Development Eksekusi

Status: 27 Oktober 2026  
Blueprint: `docs/BACKLOG.md` v1.1  
Prinsip: scope, prioritas, dan cut line MVP tidak berubah. Urutan eksekusi disesuaikan dengan kondisi repo saat ini.

## 1. Posisi saat ini

Yang sudah berjalan:

- Fondasi Laravel, Breeze, Livewire, layout brand, role enum, Policy project/attachment.
- Project: CRUD, anggota, milestone, overview, filter dan pagination.
- Worklog: input harian, daftar pekerjaan, lampiran privat, feed tim, detail worklog.
- Dashboard dasar.

Yang belum selesai:

- Gap Must Sprint 1: user nonaktif masih bisa login, admin Users/Kategori/Pengaturan masih placeholder.
- Task: CRUD, workflow status, Tugas Saya, dan board belum ada.
- Audit before/after belum berjalan.
- Laporan, CSV, print view, dashboard manager, dan reminder belum ada.
- Restore, deploy produksi, backup, hardening, dan monitoring belum ada.

Kesimpulan: fitur sudah masuk area Sprint 4, tetapi fondasi Sprint 1 dan Sprint 3 belum lengkap. Rencana di bawah menutup gap tersebut tanpa mengubah scope blueprint.

## 2. Aturan eksekusi

- Satu sprint = 6 hari kerja, Senin–Sabtu, sekitar 6 jam efektif per hari.
- Setiap user story diselesaikan sampai Definition of Done blueprint: feature test, Policy test, Pint, responsive, staging.
- Commit per user story atau per kelompok kecil yang sudah lulus test.
- Jangan menambah fitur di luar backlog sebelum seluruh Must MVP selesai.
- Setiap akhir sprint jalankan:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

- Sebelum deploy, hapus `public/hot` dan pastikan `public/build` terbaru.

## 3. Ringkasan sprint

| Sprint | Fokus | Status 27 Okt 2026 | Sisa |
|---|---|---|---|
| 1 | Fondasi, auth, role, master data, staging | Selesai, kecuali deploy staging | Menunggu akses hosting |
| 2 | Project, anggota, milestone | Selesai | — |
| 3 | Task dan penyempurnaan worklog | Selesai | Integrasi filter task di worklog opsional |
| 4 | Worklog, lampiran, audit dasar, feed | Hampir selesai | Restore UI (US-33) |
| 5 | Dashboard, laporan, reminder | Selesai | — |
| 6 | Audit viewer, hardening, UAT, go-live | Siap deploy | Deploy aktual, backup di server, dan UAT menunggu akses hosting/SMTP |

Catatan eksekusi:

- Commit `c47682e` — Sprint 1: login user aktif, admin users, kategori, settings, profil.
- Commit `eef0039` — Sprint 2/3: task CRUD, workflow status, Tugas Saya, board, overdue milestone.
- Commit Sprint 4/6 — audit logging, audit viewer, batas lampiran, hapus lampiran, dan hapus entri terakhir.
- Commit Sprint 5 — metrik dashboard, laporan terfilter, CSV, print view, dan reminder email.
- Commit Sprint 6 — pemulihan data, security headers, dan dokumentasi deploy/backup/UAT.
- Seluruh fitur MVP sudah berjalan; 89 test lulus dan Pint bersih.
- Deployment aktual menunggu kredensial hosting, database, dan SMTP produksi.
- `public/hot` sudah dihapus dan asset produksi sudah dibangun.

## 4. Rencana per sprint

### Sprint 1 — Fondasi, Auth, Role, Master Data

Tujuan: seluruh Must Sprint 1 selesai dan staging bisa diakses.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-01 | Perbaiki login: user `is_active = false` wajib ditolak; pertahankan rate limit 5 percobaan/menit | Perlu perbaikan | Test login user nonaktif dan rate limit |
| US-02 | Verifikasi lupa password: email reset, tautan 60 menit, validasi password baru | Sebagian | Test reset password end-to-end |
| US-03 | Admin CRUD user: daftar, tambah, edit nama/jabatan/role/status, reset password, tidak bisa menonaktifkan diri sendiri | Belum | Halaman `/admin/users` berfungsi |
| US-04 | Finalisasi role dan Policy: member, manager, admin; manager hanya mengakses data tim; test IDOR | Sebagian | Policy test lengkap |
| US-05 | Profil: nama, jabatan, password; email hanya dapat diubah admin | Sebagian | Form profil sesuai aturan |
| US-06 | Admin CRUD kategori: nama, warna, aktif/nonaktif, kategori `Umum` dilindungi | Belum | Halaman `/admin/categories` berfungsi |
| US-07 | Pengaturan: timezone Asia/Jakarta, hari kerja Senin–Sabtu, reminder 17:00; terapkan ke runtime | Sebagian | Halaman `/admin/settings` berfungsi |
| — | Deploy staging: docroot `public`, `.env` staging, migrate, cron scheduler | Belum | URL staging dan smoke test |

Urutan kerja:

1. US-01 dan US-04 lebih dulu karena menyangkut keamanan akses.
2. US-03, US-06, US-07 karena menjadi dependensi task, worklog, dan reminder.
3. US-05 dan US-02 sebagai penyelesaian auth.
4. Deploy staging di akhir sprint.

Definition of Done Sprint 1:

- Semua test auth, role, admin CRUD, kategori, dan settings lulus.
- Member mendapat 403 di seluruh route admin.
- User nonaktif tidak bisa login dan sesi aktifnya ditolak.
- Tidak ada placeholder tersisa untuk menu admin Must.
- Staging dapat dibuka dan login admin berhasil.

### Sprint 2 — Project, Anggota, Milestone

Tujuan: memverifikasi fitur yang sudah dibangun dan menutup gap kecil.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-08 | CRUD project: kode unik, owner, tanggal, status, validasi due date | Selesai | Test CRUD dan validasi |
| US-09 | Anggota project: tambah/hapus, owner harus ditransfer sebelum keluar | Selesai | Test kepemilikan dan akses |
| US-10 | Milestone: CRUD, deadline, status, overdue otomatis | Sebagian | Tambah indikator/scope overdue |
| US-11 | Overview project: progress task, milestone, aktivitas, deadline, anggota | Selesai | Test halaman detail |
| US-12 | Daftar/filter project: status, owner, pencarian, pagination | Selesai | Test filter dan pagination |

Urutan kerja:

1. Audit kode project yang sudah ada, tambahkan test yang belum ada.
2. Tambahkan perhitungan overdue milestone.
3. Rapikan query agar tidak N+1.
4. Commit seluruh fitur project.

Definition of Done Sprint 2:

- Member hanya melihat project yang diikuti.
- Manager dan admin dapat mengelola project.
- Progress project dihitung dari task `done` dibagi total task.
- Milestone overdue tampil benar.
- Test project dan Policy lulus.

### Sprint 3 — Task dan Worklog Core

Tujuan: modul task lengkap dan worklog terhubung ke task.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-13 | CRUD task: project wajib, milestone opsional, assignee, prioritas, deadline | Belum | Halaman task dan form |
| US-14 | Workflow status: todo → in_progress → blocked/done; `completed_at`; hanya assignee atau manager | Belum | Aksi ubah status + Policy |
| US-15 | Tugas Saya: hanya task sendiri, urut deadline, filter status/overdue | Sebagian | Halaman `/tasks` |
| US-16 | Board sederhana per status, responsive, tanpa drag-and-drop | Belum | Tab board di halaman task |
| US-17 | Worklog harian: satu laporan per user/tanggal, minimal satu pekerjaan, tolak tanggal masa depan | Selesai | Test regresi |
| US-18 | Daftar pekerjaan: tambah/ubah/hapus, project/task/kategori opsional, deskripsi wajib | Selesai | Test regresi + validasi relasi task |

Urutan kerja:

1. US-13 dan US-14 karena menjadi dasar US-15, US-16, dan worklog task.
2. US-15 dan US-16.
3. Verifikasi US-17/US-18 setelah task tersedia.
4. Pastikan task yang dipilih di worklog hanya task dari project yang dipilih.

Definition of Done Sprint 3:

- Task CRUD dan workflow berjalan dengan Policy yang benar.
- Tugas Saya hanya menampilkan task milik user.
- Board menampilkan kolom status dengan benar.
- Worklog dapat memilih task dan menyimpan relasinya.
- Tidak ada durasi, jam, atau timesheet di seluruh UI.

### Sprint 4 — Worklog, Lampiran, Audit Dasar, Feed

Tujuan: worklog selesai sesuai aturan bisnis dan siap dipakai harian.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-19 | Quick add Hari Ini: satu layar, satu baris default, prefill tanggal, nyaman di HP | Selesai | Uji usability < 60 detik |
| US-20 | Edit/hapus dengan audit: audit old/new, soft delete, hapus entri terakhir menghapus laporan, riwayat perubahan | Sebagian | Audit log berjalan + halaman riwayat |
| US-21 | Lampiran: maksimal 3 per pekerjaan termasuk lampiran lama, 5 MB, JPG/PNG/WEBP/PDF, hapus lampiran, akses ber-Policy | Sebagian | Penyempurnaan validasi dan hapus |
| US-22 | Feed tim: kronologis, jumlah pekerjaan, chip project/kategori, filter tanggal/anggota, pagination | Selesai | Test filter dan pagination |
| US-23 | Riwayat worklog: filter tanggal, project, anggota | Selesai | Verifikasi cakupan filter |

Urutan kerja:

1. Aktifkan pencatatan audit pada `DailyReport`, `WorkLogEntry`, `Project`, `Task`, `User`, `Category`, `Setting`, dan `Attachment`.
2. Selesaikan aturan hapus: hapus entri terakhir → soft delete laporan; tambahkan test.
3. Perbaiki validasi lampiran agar total maksimal 3 menghitung lampiran existing; tambahkan aksi hapus.
4. Uji alur quick add di mobile dan ukur waktu input.
5. Rapikan feed dan riwayat.

Definition of Done Sprint 4:

- Semua perubahan worklog tercatat di audit log dengan nilai lama dan baru.
- Soft delete berjalan dan data masih dapat dipulihkan oleh admin pada Sprint 6.
- Lampiran tidak bisa melebihi 3 file per pekerjaan.
- Feed dan riwayat menghormati hak akses.
- Input worklog selesai di bawah 60 detik pada perangkat mobile.

### Sprint 5 — Dashboard, Laporan, Reminder

Tujuan: visibilitas aktivitas tim dan pengingat harian berjalan.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-24 | Dashboard pribadi: status hari ini, jumlah pekerjaan minggu ini, hari terisi, task terbuka, worklog terbaru | Sebagian | Lengkapi metrik |
| US-25 | Dashboard manager: siapa belum isi hari ini, jumlah pekerjaan per anggota, task overdue, health project | Belum | Tampilan khusus manager/admin |
| US-26 | Laporan terfilter: tanggal, user, project, kategori; jumlah hari terisi dan pekerjaan; scope role | Belum | Halaman `/reports` |
| US-27 | Export CSV mengikuti filter, streamed download, nama file bertanggal | Belum | Tombol export + test |
| US-28 | Print view halaman laporan | Belum | CSS print dan route |
| US-29 | Aktivitas per anggota: jumlah pekerjaan, hari terisi, project terlibat | Belum | Ringkasan per anggota |
| US-30 | Progres project: task selesai/total, milestone, overdue, aktivitas terbaru | Sebagian | Verifikasi dan lengkapi |
| US-31 | Email reminder: Senin–Sabtu 17:00, hanya user tanpa laporan, sekali per hari, tautan form, status kirim dicatat | Belum | Command + scheduler + mail + log |

Urutan kerja:

1. US-24 dan US-25 lebih dulu karena memakai query yang sama dengan laporan.
2. US-26 dan US-27.
3. US-28 dan US-29.
4. US-31 setelah settings dan queue/scheduler siap.
5. US-30 diverifikasi bersama project.

Definition of Done Sprint 5:

- Angka dashboard dan laporan konsisten dengan data worklog.
- Member hanya melihat data dirinya; manager melihat tim; admin melihat semua.
- CSV dapat dibuka di Excel dan sesuai filter.
- Print view bersih tanpa sidebar.
- Reminder tidak terkirim dua kali dan tidak terkirim untuk user yang sudah mengisi.

### Sprint 6 — Audit Viewer, Hardening, UAT, Go-Live

Tujuan: aplikasi aman, terpantau, dan siap produksi.

| ID | Pekerjaan | Status | Output |
|---|---|---|---|
| US-32 | Audit log viewer: filter user/event/tanggal, tampilkan old/new, hanya admin | Belum | Halaman `/admin/audit` |
| US-33 | Restore data soft-deleted: worklog, pekerjaan, lampiran | Belum | Aksi restore khusus admin |
| US-34 | Deploy produksi: docroot `public`, `.env` production, migrate, cron scheduler/queue, HTTPS | Belum | Checklist deploy |
| US-35 | Backup/restore: database + storage harian, prosedur restore diuji | Belum | Script + dokumentasi |
| US-36 | Security hardening: `APP_DEBUG=false`, secure headers, session aman, rate limit, hapus `public/hot` | Sebagian | Checklist hardening |
| US-37 | Monitoring: uptime check dan review error log | Belum | Minimal satu uptime monitor |

Urutan kerja:

1. US-32 dan US-33.
2. Hardening dan konfigurasi produksi.
3. Deploy staging final, UAT bersama manager dan satu anggota.
4. Backup/restore diuji di staging.
5. Go-live dan monitoring.

Definition of Done Sprint 6:

- Audit viewer menampilkan seluruh perubahan penting.
- Restore data berhasil dan tercatat di audit.
- Produksi berjalan dengan HTTPS, cron, queue, dan email.
- Backup harian berjalan; restore sudah diuji.
- UAT selesai dan tidak ada bug Must yang terbuka.

## 5. Checklist lintas sprint

### Keamanan

- Semua route data memakai middleware `auth`.
- Semua akses record memakai Policy.
- User nonaktif ditolak di login dan request berikutnya.
- Validasi upload: tipe, ukuran, jumlah.
- Tidak ada data sensitif di log.
- `APP_DEBUG=false` di produksi.

### Performa

- List project, task, worklog, feed, dan laporan memakai eager loading.
- Pagination pada semua list.
- Tidak ada query di dalam Blade loop.
- Index database untuk filter tanggal, user, project, dan status.

### Kualitas

- Feature test untuk alur utama.
- Policy test untuk akses lintas role.
- Pint bersih.
- Tidak ada placeholder pada menu Must.
- Build asset produksi tersedia.

## 6. Dependensi utama

| Pekerjaan | Dependensi |
|---|---|
| Task | Admin user dan project selesai |
| Worklog → task | Task CRUD selesai |
| Audit viewer | Audit logging Sprint 4 selesai |
| Reminder | Settings dan konfigurasi mail selesai |
| Laporan | Worklog dan task stabil |
| Deploy | Staging, `.env`, database, cron, SMTP siap |
| UAT | Seluruh Must selesai |

## 7. Keputusan yang masih menunggu

- Alamat email pengirim dan kredensial SMTP.
- Daftar anggota dan admin pertama.
- Apakah hari libur nasional dikecualikan dari reminder.
- Retensi lampiran dan audit log.
- Apakah manager boleh mengedit worklog anggota.
- Format laporan yang paling dibutuhkan Kepala IT.
- Apakah kategori wajib atau tetap opsional.
- Apakah mode gelap masuk fase berikutnya.

## 8. Cut line jika waktu mepet

Tunda tanpa mengganggu alur utama:

- US-05 Profil lanjutan.
- US-12 Filter project lanjutan.
- US-16 Board sederhana.
- US-23 Riwayat worklog lanjutan.
- US-28 Print view.
- US-29 Aktivitas per anggota.
- US-30 Progres project lanjutan.
- US-33 Restore data.
- US-37 Monitoring.

Seluruh item Must di Sprint 1–6 tetap wajib selesai sebelum go-live.

## 9. Target urutan pengerjaan

| Urutan | Fokus | Estimasi |
|---|---|---|
| 1 | Commit pekerjaan yang ada + hapus `public/hot` | 0,5 hari |
| 2 | Sprint 1: auth, admin, kategori, settings, staging | 6 hari |
| 3 | Sprint 2: verifikasi project + overdue milestone | 1–2 hari |
| 4 | Sprint 3: task dan integrasi worklog | 5–6 hari |
| 5 | Sprint 4: audit, lampiran, aturan hapus | 5 hari |
| 6 | Sprint 5: dashboard, laporan, CSV, reminder | 6 hari |
| 7 | Sprint 6: audit viewer, hardening, UAT, go-live | 6 hari |

Total sisa pekerjaan: sekitar 29–31 hari kerja, tergantung kesiapan staging, SMTP, dan UAT.
