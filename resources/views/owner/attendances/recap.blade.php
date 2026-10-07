<!-- resources/views/owner/attendances/recap.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi & Penggajian')

@section('content')
<div class="space-y-6">

    <!-- Filter Periode -->
    <form method="GET" action="{{ route('owner.attendances.recap') }}" class="flex items-end gap-3">
        <div>
            <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ \Carbon\Carbon::parse($startDate)->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ \Carbon\Carbon::parse($endDate)->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
        </div>
        <button type="submit" class="bg-[#0f2137] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#183252] transition">Tampilkan</button>
    </form>

    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Karyawan Tercatat</p>
            <h3 class="text-lg font-bold text-gray-800">{{ $recapData->count() }} <span class="text-xs font-medium text-gray-500">Orang</span></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Total Hari Hadir</p>
            <h3 class="text-lg font-bold text-gray-800">{{ $recapData->sum('days_present') }} <span class="text-xs font-medium text-gray-500">Hari</span></h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Total Gaji Periode</p>
            <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($recapData->sum('total_salary'), 0, ',', '.') }}</h3>
        </div>
    </div>

    <!-- Tabel Rekap Presensi & Gaji -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-sm">Rekap Presensi & Gaji</h3>
            <p class="text-[10px] text-gray-500 mt-0.5">Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold text-[10px]">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Hari Hadir</th>
                    <th class="px-6 py-4">Jam Reguler</th>
                    <th class="px-6 py-4">Lembur 2x</th>
                    <th class="px-6 py-4">Lembur 3x</th>
                    <th class="px-6 py-4">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @forelse($recapData as $row)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">{{ $row['employee']->name }}</td>
                    <td class="px-6 py-4">{{ $row['days_present'] }} hari</td>
                    <td class="px-6 py-4">{{ $row['total_regular_hours'] }} jam</td>
                    <td class="px-6 py-4">{{ $row['overtime_1x'] }} jam</td>
                    <td class="px-6 py-4">{{ $row['overtime_2x'] }} jam</td>
                    <td class="px-6 py-4 font-bold text-gray-800">Rp {{ number_format($row['total_salary'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data presensi pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection