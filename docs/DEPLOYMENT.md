# Deployment, Backup, dan UAT — IT Worklog

Dokumen ini dipakai untuk deploy MVP ke shared hosting cPanel.

## 1. Prasyarat

- PHP 8.3 atau lebih baru dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`, `curl`.
- MySQL 8.
- Docroot domain diarahkan ke folder `public`.
- Cron setiap menit tersedia.
- SMTP cPanel dengan SPF dan DKIM.
- Akses SSH atau Terminal cPanel.

## 2. Persiapan lokal

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan test
./vendor/bin/pint --test
```

Upload source tanpa `node_modules`, `.env`, `storage/logs`, dan `public/hot`.

## 3. Environment produksi

Buat `.env` di server:

```dotenv
APP_NAME="IT Worklog"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://worklog.domain-perusahaan.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.domain-perusahaan.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="admin.it@borneoprima.com"
MAIL_FROM_NAME="IT Worklog"

ADMIN_NAME="Administrator IT"
ADMIN_EMAIL=...
ADMIN_PASSWORD=...
```

Jika `APP_KEY` belum ada:

```bash
php artisan key:generate --force
```

## 4. Instalasi di server

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan permission:

```bash
chmod -R ug+rwx storage bootstrap/cache
```

Jangan jalankan `migrate:fresh` di produksi.

## 5. Cron

Tambahkan lewat cPanel → Cron Jobs, setiap menit:

```cron
* * * * * cd /home/USER/domains/worklog.domain-perusahaan.com && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Scheduler menjalankan:

- `worklog:send-reminders` setiap menit; command memeriksa hari kerja dan jam dari `settings`.
- `reports:send-scheduled` setiap menit; command mengirim laporan harian, mingguan, atau bulanan sesuai `settings` kepada manager dan admin aktif.
- Queue worker jika nanti ada job queued; saat ini pengiriman email reminder dan laporan berjalan sinkron.

Uji manual:

```bash
php artisan worklog:send-reminders
php artisan reports:send-scheduled
php artisan schedule:list
```

## 6. Pemeriksaan setelah deploy

- Buka `/login`; pastikan HTTPS aktif.
- Login admin.
- Cek `/admin/users`, `/admin/categories`, `/admin/settings`.
- Isi worklog uji, lalu cek `/feed`, `/reports`, export CSV, print view.
- Buat task uji, ubah status sampai selesai.
- Cari worklog, project, task, dan anggota melalui pencarian global.
- Buka pusat notifikasi setelah task di-assign atau anggota disebut di komentar.
- Coba filter task per project dan drag-and-drop di board.
- Aktifkan laporan terjadwal, lalu jalankan `php artisan reports:send-scheduled`.
- Cek `/admin/audit` dan `/admin/trash`.
- Kirim reminder uji ke satu akun.
- Pastikan `APP_DEBUG=false` dan error tidak tampil ke pengguna.

## 7. Backup

### Database

```bash
mysqldump -u USER -p --single-transaction --routines --triggers DATABASE > backup-$(date +%F).sql
```

Rekomendasi cron harian pukul 01:00:

```cron
0 1 * * * mysqldump -u USER -p'PASSWORD' --single-transaction DATABASE | gzip > /home/USER/backups/db-$(date +\%F).sql.gz
```

### Storage privat

```bash
tar -czf /home/USER/backups/storage-$(date +%F).tar.gz storage/app/private
```

Simpan backup di luar server atau object storage. Retensi minimal 14 hari.

## 8. Restore

```bash
gunzip < db-2026-10-27.sql.gz | mysql -u USER -p DATABASE
tar -xzf storage-2026-10-27.tar.gz -C /home/USER/domains/worklog.domain-perusahaan.com
php artisan migrate --force
php artisan optimize:clear
```

Uji restore di staging sebelum go-live.

## 9. Rollback

1. Aktifkan maintenance mode: `php artisan down`.
2. Kembalikan kode ke commit stabil sebelumnya.
3. Restore database jika migration terakhir tidak kompatibel.
4. `php artisan optimize:clear && php artisan config:cache && php artisan route:cache`.
5. `php artisan up`.

## 10. UAT checklist

- [ ] Login anggota, manager, admin.
- [ ] User nonaktif tidak bisa login.
- [ ] Admin menambah user dan reset password.
- [ ] Admin menambah kategori dan menonaktifkannya.
- [ ] Manager membuat project, anggota, milestone.
- [ ] Manager membuat task dan assign ke anggota.
- [ ] Anggota mengubah status task sampai selesai.
- [ ] Anggota mengisi worklog dengan minimal satu pekerjaan.
- [ ] Worklog muncul di feed tim.
- [ ] Lampiran maksimal 3 file, 5 MB per file.
- [ ] Laporan terfilter menampilkan jumlah pekerjaan dan hari terisi.
- [ ] Export CSV terbuka di Excel.
- [ ] Print view bersih saat disimpan sebagai PDF.
- [ ] Reminder hanya terkirim ke user yang belum mengisi.
- [ ] Pencarian global menghormati hak akses anggota.
- [ ] Notifikasi task dan mention tampil serta bisa ditandai dibaca.
- [ ] Komentar dan mention berfungsi pada task dan worklog.
- [ ] Board bisa diubah lewat drag-and-drop dan tombol status.
- [ ] Laporan terjadwal terkirim ke manager/admin dengan lampiran CSV.
- [ ] Audit log menampilkan nilai lama dan baru.
- [ ] Admin memulihkan worklog/lampiran yang dihapus.
- [ ] Backup dan restore diuji di staging.

## 11. Catatan keamanan

- Jangan commit `.env` atau kredensial SMTP.
- Gunakan HTTPS dan `SESSION_SECURE_COOKIE=true`.
- Batasi akses edit langsung ke database.
- Hapus `public/hot` sebelum upload.
- Jalankan `composer audit` secara berkala.
- Review `/admin/audit` untuk perubahan role dan penghapusan data.
