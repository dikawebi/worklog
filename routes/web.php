<?php

use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectShowController;
use App\Http\Controllers\WorklogController;
use App\Http\Controllers\WorklogShowController;
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
    Route::get('/attachments/{attachment}/download', AttachmentDownloadController::class)
        ->name('attachments.download');

    Route::get('/worklogs/{date?}', WorklogController::class)
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('worklogs.index');

    Route::view('/feed', 'feed.index')->name('feed');

    Route::get('/worklogs/report/{report}', WorklogShowController::class)
        ->name('worklogs.show');

    Route::view('/projects', 'projects.index')->name('projects.index');

    Route::get('/projects/{project}', ProjectShowController::class)->name('projects.show');

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
