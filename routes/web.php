<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FinancialController;

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

// Route Utama / Welcome
Route::get('/', function () {
    return view('welcome');
});

// Route Keuangan & Laporan (Lengkap dengan Named Route agar sesuai dengan view Anda)
Route::get('/keuangan', [FinancialController::class, 'index']);
Route::post('/keuangan/store', [FinancialController::class, 'store'])->name('financial.store');
Route::put('/keuangan/update/{id}', [FinancialController::class, 'update'])->name('financial.update');
Route::delete('/keuangan/delete/{id}', [FinancialController::class, 'destroy'])->name('financial.destroy');