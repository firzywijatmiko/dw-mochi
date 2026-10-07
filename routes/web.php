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
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang membutuhkan autentikasi
Route::middleware(['auth'])->group(function () {
    
    // --- GRUP OWNER / ADMIN ---
    Route::middleware(['role:Owner,Admin'])->prefix('owner')->name('owner.')->group(function () {
        // Rute Dashboard Statistik Owner[cite: 25]
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        // Rute Rekap Presensi dan Gaji Owner[cite: 24]
        Route::get('/attendances/recap', [\App\Http\Controllers\AttendanceController::class, 'recap'])->name('attendances.recap');
        // Rute CRUD Pesanan standar
        Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['create', 'edit', 'show']);
        // Rute Custom untuk Parsing WA & Print Label
        Route::post('/orders/parse-wa', [\App\Http\Controllers\OrderController::class, 'parseWhatsApp'])->name('orders.parse_wa');
        Route::get('/orders/{order}/print-label', [\App\Http\Controllers\OrderController::class, 'printLabel'])->name('orders.print_label');
        // Rute CRUD Karyawan
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['create', 'edit', 'show']);
        // Rute CRUD Pengeluaran Operasional[cite: 19, 20, 21]
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['create', 'edit', 'show']);
        // Rute Laporan Operasional Mingguan[cite: 22]
        Route::get('/reports/weekly', [\App\Http\Controllers\ReportController::class, 'weeklyReport'])->name('reports.weekly');
        // Rute CRUD Product Variant (Disesuaikan menggunakan except seperti Employees)
        Route::resource('product_variants', ProductVariantController::class)->except(['create', 'edit', 'show']);    });
        
    // --- GRUP KARYAWAN ---
    Route::middleware(['role:Karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', function () {
            return 'Ini Halaman Dashboard Karyawan DW Mochi'; // Ganti dengan view('karyawan.dashboard') nanti
        })->name('dashboard');
        // Rute Presensi (Menggunakan POST karena akan dikirim via AJAX dari titik koordinat perangkat)
        Route::post('/attendances/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn'])->name('attendances.check_in');
        Route::post('/attendances/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('attendances.check_out');
        // Rute Lihat Riwayat & Total Gaji Berjalan Karyawan[cite: 23]
        Route::get('/salary-history', [\App\Http\Controllers\AttendanceController::class, 'salaryHistory'])->name('salary_history');
    });
});
// Route Keuangan
Route::middleware(['auth', 'role:Owner,Admin'])->group(function () {
    Route::get('/keuangan', [FinancialController::class, 'index'])->name('financial.index');
    Route::post('/keuangan/store', [FinancialController::class, 'store'])->name('financial.store');
    Route::put('/keuangan/update/{id}', [FinancialController::class, 'update'])->name('financial.update');
    Route::delete('/keuangan/delete/{id}', [FinancialController::class, 'destroy'])->name('financial.destroy');
});