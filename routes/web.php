<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\ProductVariantController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return \Illuminate\Support\Facades\Auth::user()->role === 'Karyawan'
            ? redirect()->route('karyawan.dashboard')
            : redirect()->route('owner.dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang membutuhkan autentikasi
Route::middleware(['auth'])->group(function () {

    Route::get('/keuangan', [FinancialController::class, 'index']);
    Route::post('/keuangan/store', [FinancialController::class, 'store']);
    Route::put('/keuangan/update/{id}', [FinancialController::class, 'update']);
    Route::delete('/keuangan/delete/{id}', [FinancialController::class, 'destroy']);

    // --- GRUP OWNER / ADMIN ---
    Route::middleware(['role:Owner,Admin'])->prefix('owner')->name('owner.')->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendances/recap', [\App\Http\Controllers\AttendanceController::class, 'recap'])->name('attendances.recap');
        Route::get('/payrolls', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payrolls.index');
        Route::put('/payrolls/{payroll}/status', [\App\Http\Controllers\PayrollController::class, 'updateStatus'])->name('payrolls.status');
        
        // Rute CRUD Pesanan standar
        Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['create', 'edit', 'show']);

        // Rute Custom untuk Parsing WA & Print Label (Diperbaiki agar rute GET tidak dobel prefiks owner)
        Route::post('/orders/parse-wa', [\App\Http\Controllers\OrderController::class, 'parseWhatsApp'])->name('orders.parse_wa');
        Route::get('/orders/parse-wa', function () {
            return redirect()->route('owner.orders.index')->with('error', 'Silakan masukkan teks WhatsApp melalui tombol "Dari WhatsApp" di halaman pesanan.');
        });
        
        Route::get('/orders/{order}/print-label', [\App\Http\Controllers\OrderController::class, 'printLabel'])->name('orders.print_label');
        Route::post('/orders/print-bulk', [\App\Http\Controllers\OrderController::class, 'printSelectedLabels'])->name('orders.print_bulk');

        Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['create', 'edit', 'show']);
        Route::get('/reports/weekly', [\App\Http\Controllers\ReportController::class, 'weeklyReport'])->name('reports.weekly');
        Route::resource('product_variants', ProductVariantController::class)->except(['create', 'edit', 'show']);

        Route::get('/keuangan', [FinancialController::class, 'index'])->name('financial.index');
        Route::post('/keuangan/store', [FinancialController::class, 'store'])->name('financial.store');
        Route::put('/keuangan/update/{id}', [FinancialController::class, 'update'])->name('financial.update');
        Route::delete('/keuangan/delete/{id}', [FinancialController::class, 'destroy'])->name('financial.destroy');
    });

    // --- GRUP KARYAWAN ---
    Route::middleware(['role:Karyawan'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'employeeDashboard'])->name('dashboard');
        Route::post('/attendances/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('attendances.check_in');
        Route::post('/attendances/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('attendances.check_out');
        Route::get('/salary-history', [\App\Http\Controllers\AttendanceController::class, 'salaryHistory'])->name('salary_history');
    });
});