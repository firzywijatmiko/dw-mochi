<!-- resources/views/owner/dashboard.blade.php -->
@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header & Welcome Message -->
    <div>
        <h2 class="text-xl font-bold text-gray-800">Selamat datang, {{ Auth::user()->name }}! 🍪</h2>
        <p class="text-xs text-gray-500 mt-1">Berikut adalah ringkasan performa operasional DW Mochi hari ini.</p>
    </div>

    <!-- Grid Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        
        <!-- Kartu Pendapatan Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
            <div class="p-3 bg-green-50 text-green-600 rounded-lg flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pendapatan Hari Ini</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($statistics['today_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Kartu Pengeluaran Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
            <div class="p-3 bg-red-50 text-red-500 rounded-lg flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Bahan Baku (Hari Ini)</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($statistics['today_expense'], 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Kartu Pesanan Aktif -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
            <div class="p-3 bg-orange-50 text-orange-500 rounded-lg flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pesanan Aktif</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $statistics['active_orders'] }} <span class="text-xs font-medium text-gray-500">Antrean</span></h3>
            </div>
        </div>

        <!-- Kartu Pesanan Selesai -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
            <div class="p-3 bg-blue-50 text-blue-500 rounded-lg flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Selesai Hari Ini</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $statistics['completed_today'] }} <span class="text-xs font-medium text-gray-500">Pesanan</span></h3>
            </div>
        </div>

        <!-- Kartu Karyawan Hadir -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 transition hover:shadow-md">
            <div class="p-3 bg-[#0f2137]/10 text-[#0f2137] rounded-lg flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Karyawan Hadir</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $statistics['present_employees'] }} <span class="text-xs font-medium text-gray-500">Orang</span></h3>
            </div>
        </div>

    </div>

    <!-- Area Opsional untuk Tabel/Grafik Nantinya -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-64 flex flex-col items-center justify-center text-gray-400">
        <svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        <p class="text-sm font-medium">Area Visualisasi Data (Opsional)</p>
        <p class="text-xs">Bisa diisi grafik tren pesanan atau rekap mingguan nanti.</p>
    </div>

</div>
@endsection