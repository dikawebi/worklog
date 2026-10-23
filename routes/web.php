<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::view('/worklogs', 'placeholder', [
        'title' => 'Worklog',
        'description' => 'Catat daftar pekerjaan harian tanpa durasi. Modul ini akan diisi pada sprint berikutnya.',
        'icon' => 'worklog',
    ])->name('worklogs.index');

    Route::view('/feed', 'placeholder', [
        'title' => 'Aktivitas Tim',
        'description' => 'Lihat pekerjaan harian yang dicatat seluruh anggota tim IT.',
        'icon' => 'feed',
    ])->name('feed');

    Route::view('/projects', 'placeholder', [
        'title' => 'Projects',
        'description' => 'Kelola project, milestone, anggota, dan progres pekerjaan jangka panjang.',
        'icon' => 'projects',
    ])->name('projects.index');

    Route::view('/tasks', 'placeholder', [
        'title' => 'Tugas',
        'description' => 'Lihat dan kelola tugas yang sedang dikerjakan tim.',
        'icon' => 'tasks',
    ])->name('tasks.index');

    Route::view('/reports', 'placeholder', [
        'title' => 'Laporan',
        'description' => 'Ringkasan hari terisi, jumlah pekerjaan, dan progres project.',
        'icon' => 'reports',
    ])->name('reports.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/users', 'placeholder', [
            'title' => 'Users',
            'description' => 'Kelola akun, role, jabatan, dan status aktif anggota tim.',
            'icon' => 'users',
        ])->name('users');

        Route::view('/categories', 'placeholder', [
            'title' => 'Kategori',
            'description' => 'Kelola kategori pekerjaan seperti Development, Support, dan Meeting.',
            'icon' => 'categories',
        ])->name('categories');

        Route::view('/settings', 'placeholder', [
            'title' => 'Pengaturan',
            'description' => 'Atur zona waktu, hari kerja, dan jadwal pengingat worklog.',
            'icon' => 'settings',
        ])->name('settings');

        Route::view('/audit', 'placeholder', [
            'title' => 'Audit Log',
            'description' => 'Telusuri perubahan data penting beserta pelaku dan waktunya.',
            'icon' => 'audit',
        ])->name('audit');
    });
});

require __DIR__.'/auth.php';
