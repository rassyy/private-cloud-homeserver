<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public ──
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Authenticated Routes ──
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard redirects to file browser
    Route::get('/dashboard', function () {
        return redirect()->route('folders.index');
    })->name('dashboard');

    // ── Views: Starred, Recent, Shared, Trash ──
    Route::get('/starred', [FolderController::class, 'starred'])->name('folders.starred');
    Route::get('/recent', [FolderController::class, 'recent'])->name('folders.recent');
    Route::get('/shared', [FolderController::class, 'shared'])->name('folders.shared');
    Route::get('/trash', [FolderController::class, 'trash'])->name('folders.trash');
    Route::post('/trash/empty', [FolderController::class, 'emptyTrash'])->name('folders.emptyTrash');

    // ── Folder Navigation & CRUD ──
    Route::get('/files', [FolderController::class, 'index'])->name('folders.index');
    Route::get('/folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::post('/folders/{folder}/star', [FolderController::class, 'toggleStar'])->name('folders.star');
    Route::post('/folders/{folder}/share', [FolderController::class, 'share'])->name('folders.share');
    Route::post('/folders/{folder}/move', [FolderController::class, 'move'])->name('folders.move');
    Route::post('/folders/{id}/restore', [FolderController::class, 'restore'])->name('folders.restore');
    Route::delete('/folders/{id}/force-delete', [FolderController::class, 'forceDelete'])->name('folders.forceDelete');

    // ── File Operations ──
    Route::post('/files/upload', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{file}/details', [FileController::class, 'show'])->name('files.show');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::patch('/files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/{file}/star', [FileController::class, 'toggleStar'])->name('files.star');
    Route::post('/files/{file}/share', [FileController::class, 'share'])->name('files.share');
    Route::post('/files/{file}/move', [FileController::class, 'move'])->name('files.move');
    Route::post('/files/{id}/restore', [FileController::class, 'restore'])->name('files.restore');
    Route::delete('/files/{id}/force-delete', [FileController::class, 'forceDelete'])->name('files.forceDelete');

    // ── Profile ──
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
