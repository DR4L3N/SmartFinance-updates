<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreferencesController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('transactions', App\Http\Controllers\TransactionController::class)
        ->except(['show']);
    Route::get('/transactions/export/csv', [App\Http\Controllers\TransactionController::class, 'export'])
        ->name('transactions.export');

    Route::get('/preferences', [PreferencesController::class, 'edit'])->name('preferences.edit');
    Route::patch('/preferences', [PreferencesController::class, 'update'])->name('preferences.update');
});


require __DIR__.'/auth.php';
