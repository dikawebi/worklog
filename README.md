# IT Worklog — PT Borneo Prima

Aplikasi internal untuk mencatat daftar pekerjaan harian tim IT, memantau aktivitas, dan melacak progres project.

## Stack

- Laravel 13 + PHP 8.3
- Blade + Livewire (menyusul pada sprint fitur)
- MySQL 8
- Tailwind CSS via Vite
- Spatie Activity Log
- Tanpa runtime Node.js di server

## Dokumentasi

- [Backlog](docs/BACKLOG.md)
- [ERD dan skema database](docs/DATABASE.md)
- [Wireframes](docs/WIREFRAMES.md)

## Setup Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate

# Buat database MySQL
mysql -u root -e "CREATE DATABASE worklog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Sesuaikan DB_* dan ADMIN_* di .env
php artisan migrate:fresh --seed

npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Admin Awal

Dibuat dari `ADMIN_NAME`, `ADMIN_EMAIL`, dan `ADMIN_PASSWORD` di `.env`.

Nilai default development ada di `.env` lokal. Jangan gunakan password default di produksi dan jangan commit file `.env`.

## Perintah Penting

```bash
php artisan test
./vendor/bin/pint
npm run build
php artisan migrate:status
```

## Struktur Utama

- `app/Enums` — enum role, status project, status task, prioritas, reminder
- `app/Models` — User, Project, Milestone, Task, DailyReport, WorkLogEntry, Attachment, Category, Setting, ReminderLog
- `database/migrations` — skema inti
- `resources/views/layouts` — shell aplikasi dan login
- `docs` — backlog, ERD, dan wireframes

## Catatan MVP

- Worklog berupa daftar pekerjaan harian tanpa durasi atau jam.
- Laporan memakai jumlah pekerjaan dan hari terisi.
- Registrasi publik dinonaktifkan; user dikelola admin.
- Lampiran disimpan privat dan diakses melalui route ber-Policy.
