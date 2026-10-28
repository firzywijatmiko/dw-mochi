<!-- resources/views/owner/attendances/recap.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div class="space-y-4">

    <!-- Filter tanggal + ringkasan -->
    <div class="flex flex-wrap items-stretch gap-3">
        <form method="GET" action="{{ route('owner.attendances.recap') }}" class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter Tanggal</label>
            <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                   class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
        </form>

        <div class="flex flex-wrap gap-3">
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-5 py-3 text-center min-w-[96px] flex-1">
                <p class="text-xl font-bold text-gray-800">{{ $summary['present'] }}</p>
                <p class="text-[10px] text-gray-500">Hadir</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-5 py-3 text-center min-w-[96px] flex-1">
                <p class="text-xl font-bold text-gray-800">{{ $summary['absent'] }}</p>
                <p class="text-[10px] text-gray-500">Tidak Hadir</p>
            </div>
            <div class="rounded-lg shadow-sm border px-5 py-3 text-center min-w-[96px] flex-1 {{ $summary['outside'] > 0 ? 'bg-red-50 border-red-100' : 'bg-white border-[#e7e0d8]' }}">
                <p class="text-xl font-bold {{ $summary['outside'] > 0 ? 'text-red-500' : 'text-gray-800' }}">{{ $summary['outside'] }}</p>
                <p class="text-[10px] {{ $summary['outside'] > 0 ? 'text-red-500' : 'text-gray-500' }}">Lokasi ✗</p>
            </div>
        </div>
    </div>

    <!-- Tabel presensi -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[700px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Jam Masuk</th>
                    <th class="px-4 py-3">Jam Pulang</th>
                    <th class="px-4 py-3">Jam Lembur</th>
                    <th class="px-4 py-3">Status Lokasi</th>
                    <th class="px-4 py-3">Gaji Harian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                @forelse($attendances as $att)
                @php
                    $name     = $att->employee->name ?? '-';
                    $initials = collect(explode(' ', $name))->take(2)->map(function ($w) { return mb_strtoupper(mb_substr($w, 0, 1)); })->implode('');
                    $overtime = $att->overtime_1x + $att->overtime_2x;
                    $outside  = ($att->location_status ?? 'Terverifikasi') === 'Luar Radius';
                @endphp
                <tr class="hover:bg-[#fcfbf9] transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 text-[9px] font-semibold flex items-center justify-center">{{ $initials }}</span>
                            <span class="text-gray-800 font-medium">{{ $name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ optional($att->check_in_at)->format('H:i') ?? '-' }}</td>
                    <td class="px-4 py-3">{{ optional($att->check_out_at)->format('H:i') ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $overtime > 0 ? rtrim(rtrim(number_format($overtime, 2, ',', '.'), '0'), ',') . ' jam' : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($outside)
                            <span class="inline-flex rounded-full bg-red-50 px-2 py-1 text-[9px] font-semibold text-red-500">✗ Luar Radius</span>
                        @else
                            <span class="inline-flex rounded-full bg-[#dff5e9] px-2 py-1 text-[9px] font-semibold text-[#348b66]">✓ Terverifikasi</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($outside)
                            <p class="text-gray-400">Rp0</p>
                            <p class="text-[9px] text-red-500">Ditolak lokasi</p>
                        @else
                            <p class="text-gray-800 font-semibold">Rp{{ number_format($att->daily_wage, 0, ',', '.') }}</p>
                            <p class="text-[9px] text-gray-400">Gaji hari ini</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data presensi pada tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection