<!-- resources/views/owner/reports/weekly.blade.php -->
@extends('layouts.app')

@section('header_title', 'Laporan Operasional')

@section('content')
<div class="space-y-6">

    <!-- Filter Periode + Unduh PDF -->
    <div class="flex flex-wrap items-end justify-between gap-3">
        <form method="GET" action="{{ route('owner.reports.weekly') }}" class="flex items-end gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
            </div>
            <button type="submit" class="bg-[#0f2137] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#183252] transition">Tampilkan</button>
        </form>

        <a href="{{ route('owner.reports.weekly', ['start_date' => $startDate, 'end_date' => $endDate, 'download' => 1]) }}"
           class="border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-xs font-medium hover:bg-gray-50 transition">
            Unduh PDF
        </a>
    </div>

    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pesanan Selesai</p>
            <h3 class="text-lg font-bold text-gray-800">{{ $reportData['total_orders'] }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pendapatan</p>
            <h3 class="text-lg font-bold text-green-600">Rp {{ number_format($reportData['total_revenue'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Bahan Baku</p>
            <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($reportData['total_material_expense'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Gaji Karyawan</p>
            <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($reportData['total_wage_expense'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Laba Bersih</p>
            <h3 class="text-lg font-bold {{ $reportData['net_profit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
                Rp {{ number_format($reportData['net_profit'], 0, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- Rincian Harian -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-sm">Rincian Harian</h3>
            <p class="text-[10px] text-gray-500 mt-0.5">
                Periode {{ \Carbon\Carbon::parse($reportData['week_start'])->translatedFormat('d M Y') }} -
                {{ \Carbon\Carbon::parse($reportData['week_end'])->translatedFormat('d M Y') }}
            </p>
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold text-[10px]">
                <tr>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Pesanan</th>
                    <th class="px-6 py-4">Pendapatan</th>
                    <th class="px-6 py-4">Bahan Baku</th>
                    <th class="px-6 py-4">Gaji</th>
                    <th class="px-6 py-4">Laba</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @foreach($reportData['daily'] as $row)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('D, d M Y') }}</td>
                    <td class="px-6 py-4">{{ $row['orders'] }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($row['material_expense'], 0, ',', '.') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($row['wage_expense'], 0, ',', '.') }}</td>
                    <td class="px-6 py-4 {{ $row['profit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-[10px] text-gray-400">Dibuat pada {{ $reportData['generated_at']->translatedFormat('d M Y H:i') }}</p>
</div>
@endsection