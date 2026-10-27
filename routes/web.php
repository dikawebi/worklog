<?php

use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectShowController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ReportPrintController;
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

    Route::view('/tasks', 'tasks.index')->name('tasks.index');

    Route::view('/reports', 'reports.index')->name('reports.index');
    Route::get('/reports/export', ReportExportController::class)->name('reports.export');
    Route::get('/reports/print', ReportPrintController::class)->name('reports.print');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/users', 'admin.users')->name('users');
        Route::view('/categories', 'admin.categories')->name('categories');
        Route::view('/settings', 'admin.settings')->name('settings');

        Route::view('/audit', 'admin.audit')->name('audit');
    });
});

require __DIR__.'/auth.php';
