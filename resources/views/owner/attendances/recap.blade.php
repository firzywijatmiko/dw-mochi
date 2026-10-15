<!-- resources/views/owner/attendances/recap.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi')

@section('content')
<div class="space-y-6">

    <!-- Filter tanggal + ringkasan -->
    <div class="flex flex-wrap items-stretch gap-3">
        <form method="GET" action="{{ route('owner.attendances.recap') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Tanggal</label>
            <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                   class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-3 text-center min-w-[96px]">
            <p class="text-xl font-bold text-gray-800">{{ $summary['present'] }}</p>
            <p class="text-[10px] text-gray-500">Hadir</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-3 text-center min-w-[96px]">
            <p class="text-xl font-bold text-gray-800">{{ $summary['absent'] }}</p>
            <p class="text-[10px] text-gray-500">Tidak Hadir</p>
        </div>
        <div class="rounded-xl shadow-sm border px-6 py-3 text-center min-w-[96px] {{ $summary['outside'] > 0 ? 'bg-red-50 border-red-100' : 'bg-white border-gray-100' }}">
            <p class="text-xl font-bold {{ $summary['outside'] > 0 ? 'text-red-500' : 'text-gray-800' }}">{{ $summary['outside'] }}</p>
            <p class="text-[10px] {{ $summary['outside'] > 0 ? 'text-red-500' : 'text-gray-500' }}">Lokasi ✗</p>
        </div>
    </div>

    <!-- Tabel presensi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 uppercase font-semibold text-[10px] text-[#c28455]">
                <tr>
                    <th class="px-6 py-4">Nama Karyawan</th>
                    <th class="px-6 py-4">Jam Masuk</th>
                    <th class="px-6 py-4">Jam Pulang</th>
                    <th class="px-6 py-4">Jam Lembur</th>
                    <th class="px-6 py-4">Status Lokasi</th>
                    <th class="px-6 py-4">Gaji Harian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @forelse($attendances as $att)
                @php
                    $name     = $att->employee->name ?? '-';
                    $initials = collect(explode(' ', $name))->take(2)->map(function ($w) { return mb_strtoupper(mb_substr($w, 0, 1)); })->implode('');
                    $overtime = $att->overtime_1x + $att->overtime_2x;
                    $outside  = ($att->location_status ?? 'Terverifikasi') === 'Luar Radius';
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-md bg-gray-100 text-gray-600 text-[10px] font-semibold flex items-center justify-center">{{ $initials }}</span>
                            <span class="text-gray-800">{{ $name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">{{ optional($att->check_in_at)->format('H:i') ?? '-' }}</td>
                    <td class="px-6 py-4">{{ optional($att->check_out_at)->format('H:i') ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $overtime > 0 ? rtrim(rtrim(number_format($overtime, 2, ',', '.'), '0'), ',') . ' jam' : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($outside)
                            <span class="px-2.5 py-1 text-[10px] rounded-full bg-red-50 text-red-600 border border-red-100">✗ Luar Radius</span>
                        @else
                            <span class="px-2.5 py-1 text-[10px] rounded-full bg-green-50 text-green-600 border border-green-100">✓ Terverifikasi</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($outside)
                            <p class="text-gray-400">Rp0</p>
                            <p class="text-[10px] text-red-500">Ditolak lokasi</p>
                        @else
                            <p class="text-gray-800 font-semibold">Rp{{ number_format($att->daily_wage, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400">Gaji hari ini</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data presensi pada tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection