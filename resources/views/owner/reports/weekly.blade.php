<!-- resources/views/owner/reports/weekly.blade.php -->
@extends('layouts.app')

@section('header_title', 'Laporan Operasional')

@section('content')
<div class="space-y-4">

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <!-- Filter Periode + Unduh PDF -->
    <div class="flex flex-col sm:flex-row flex-wrap sm:items-end justify-between gap-3">
        <form method="GET" action="{{ route('owner.reports.weekly') }}" class="flex flex-wrap items-end gap-3 bg-white p-3 rounded-lg border border-[#e7e0d8] shadow-sm">
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
            </div>
            <button type="submit" class="bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-semibold hover:bg-[#183252] transition">Tampilkan</button>
        </form>

        @if($reportData)
        <a href="{{ route('owner.reports.weekly', ['start_date' => $reportData['week_start'], 'end_date' => $reportData['week_end'], 'download' => 1]) }}"
           class="inline-flex items-center justify-center gap-1.5 border border-gray-200 bg-white px-4 py-2 rounded-md text-xs font-semibold text-gray-600 hover:bg-gray-50 transition shadow-sm">
            <span>⎙</span> Unduh PDF
        </a>
        @endif
    </div>

    @if($reportData)
    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
            <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pesanan Selesai</p>
            <h3 class="text-base font-bold text-gray-800">{{ $reportData['total_orders'] }}</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
            <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Pendapatan</p>
            <h3 class="text-base font-bold text-[#348b66]">Rp{{ number_format($reportData['total_revenue'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
            <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Bahan Baku</p>
            <h3 class="text-base font-bold text-[#bd4d48]">Rp{{ number_format($reportData['total_material_expense'], 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
            <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Gaji Karyawan</p>
            <h3 class="text-base font-bold text-[#bd4d48]">Rp{{ number_format($reportData['total_wage_expense'], 0, ',', '.') }}</h3>
        </div>
        <div class="col-span-2 lg:col-span-1 bg-[#0f2137] rounded-lg shadow-sm p-4 text-white">
            <p class="text-[9px] font-semibold text-gray-300 uppercase tracking-wider mb-0.5">Laba Bersih</p>
            <h3 class="text-base font-bold {{ $reportData['net_profit'] >= 0 ? 'text-[#dff5e9]' : 'text-[#fee8e7]' }}">
                Rp{{ number_format($reportData['net_profit'], 0, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- Rincian Harian -->
    <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] overflow-hidden">
        <div class="px-4 py-3 border-b border-[#e7e0d8] bg-[#faf9f7]">
            <h3 class="font-bold text-[#0f2137] text-sm">Rincian Harian</h3>
            <p class="text-[10px] text-gray-500 mt-0.5">
                Periode {{ \Carbon\Carbon::parse($reportData['week_start'])->translatedFormat('d M Y') }} -
                {{ \Carbon\Carbon::parse($reportData['week_end'])->translatedFormat('d M Y') }}
            </p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-[650px] w-full text-left text-[10px]">
                <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Tanggal</th>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Pesanan</th>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Pendapatan</th>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Bahan Baku</th>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Gaji</th>
                        <th class="px-4 py-2 border-b border-[#e7e0d8]">Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                    @foreach($reportData['daily'] as $row)
                    <tr class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('D, d M Y') }}</td>
                        <td class="px-4 py-2">{{ $row['orders'] }}</td>
                        <td class="px-4 py-2">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2">Rp {{ number_format($row['material_expense'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2">Rp {{ number_format($row['wage_expense'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2 font-semibold {{ $row['profit'] >= 0 ? 'text-[#348b66]' : 'text-[#bd4d48]' }}">
                            Rp {{ number_format($row['profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-[9px] text-gray-400">Dibuat pada {{ $reportData['generated_at']->translatedFormat('d M Y H:i') }}</p>
    @endif
</div>
@endsection