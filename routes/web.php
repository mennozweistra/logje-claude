<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/glucose-data', [App\Http\Controllers\ReportsController::class, 'glucoseData'])->name('reports.glucose-data');
    Route::get('reports/weight-data', [App\Http\Controllers\ReportsController::class, 'weightData'])->name('reports.weight-data');
    Route::get('reports/exercise-data', [App\Http\Controllers\ReportsController::class, 'exerciseData'])->name('reports.exercise-data');
    Route::get('reports/nutrition-data', [App\Http\Controllers\ReportsController::class, 'nutritionData'])->name('reports.nutrition-data');
    Route::get('reports/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('reports.export');
    Route::post('reports/export/csv', [App\Http\Controllers\ReportsController::class, 'exportCsv'])->name('reports.export.csv');
    Route::post('reports/export/pdf', [App\Http\Controllers\ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
    
    Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    
    Route::get('food-management', function () {
        return view('food-management');
    })->name('food-management');
    
    Route::get('medicines-management', function () {
        return view('medicines-management');
    })->name('medicines-management');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
