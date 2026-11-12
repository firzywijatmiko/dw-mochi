<!-- resources/views/owner/attendances/recap.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div class="space-y-4">
    @if(session('success'))
        <div class="rounded-lg border border-[#c3ebd5] bg-[#edf8f2] px-4 py-3 text-xs font-semibold text-[#348b66]">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->has('sick_leave'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-600">
            {{ $errors->first('sick_leave') }}
        </div>
    @endif

    <!-- Filter rentang tanggal dan karyawan + ringkasan -->
    <div class="flex flex-wrap items-stretch gap-3">
        <form method="GET" action="{{ route('owner.attendances.recap') }}" class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Dari tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                           class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Sampai tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                           class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Karyawan</label>
                    <select name="employee_id" class="px-3 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
                        <option value="">Semua karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) request('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white hover:bg-[#183252]">
                    Tampilkan
                </button>
            </div>
        </form>

        <div class="flex flex-wrap gap-3">
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-5 py-3 text-center min-w-[96px] flex-1">
                <p class="text-xl font-bold text-gray-800">{{ $summary['present'] }}</p>
                <p class="text-[10px] text-gray-500">Hadir</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-5 py-3 text-center min-w-[96px] flex-1">
                <p class="text-xl font-bold text-[#c28455]">{{ $summary['sick_leave'] }}</p>
                <p class="text-[10px] text-gray-500">Izin Sakit</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-5 py-3 text-center min-w-[96px] flex-1">
                <p class="text-xl font-bold text-red-500">{{ $summary['absent'] }}</p>
                <p class="text-[10px] text-gray-500">Alpa</p>
            </div>
        </div>
    </div>

    <!-- Tabel presensi -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[700px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Jam Masuk</th>
                    <th class="px-4 py-3">Jam Pulang</th>
                    <!-- <th class="px-4 py-3">Durasi Kerja</th> -->
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
                    $isSickLeave = $att->attendance_status === 'izin_sakit';
                    $isRejected = $att->attendance_status === 'alpa';
                @endphp
                <tr class="hover:bg-[#fcfbf9] transition">
                    <td class="px-4 py-3">{{ $att->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 text-[9px] font-semibold flex items-center justify-center">{{ $initials }}</span>
                            <span class="text-gray-800 font-medium">{{ $name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $isSickLeave ? 'Izin sakit' : (optional($att->check_in_at)->format('H:i') ?? '-') }}</td>
                    <td class="px-4 py-3">{{ optional($att->check_out_at)->format('H:i') ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $isSickLeave ? '-' : number_format($att->worked_hours ?? 0, 2, ',', '.') . ' jam' }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $overtime > 0 ? rtrim(rtrim(number_format($overtime, 2, ',', '.'), '0'), ',') . ' jam' : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($isSickLeave || $isRejected)
                            <span class="inline-flex rounded-full {{ $isRejected ? 'bg-red-50 text-red-500' : 'bg-[#fff4e8] text-[#c28455]' }} px-2 py-1 text-[9px] font-semibold">
                                {{ $isRejected ? 'Alpa / Tanpa Keterangan' : 'Izin sakit' }}
                            </span>
                            @if($att->sick_leave_proof)
                                <a href="{{ asset('storage/' . $att->sick_leave_proof) }}" target="_blank" class="block mt-1 text-blue-600 hover:underline">Lihat bukti</a>
                            @endif
                            @if($isSickLeave && $att->sick_leave_status === 'pending')
                                <div class="flex gap-1 mt-2">
                                    <form method="POST" action="{{ route('owner.attendances.approve_sick_leave', $att) }}">
                                        @csrf
                                        <button type="submit" class="rounded bg-[#348b66] px-2 py-1 text-[9px] font-semibold text-white hover:bg-[#287252]">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('owner.attendances.reject_sick_leave', $att) }}">
                                        @csrf
                                        <button type="submit" class="rounded bg-red-500 px-2 py-1 text-[9px] font-semibold text-white hover:bg-red-600">Reject</button>
                                    </form>
                                </div>
                            @elseif($isSickLeave)
                                <span class="block mt-1 text-[9px] text-gray-400">
                                    {{ $att->sick_leave_status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            @endif
                        @elseif($outside)
                            <span class="inline-flex rounded-full bg-red-50 px-2 py-1 text-[9px] font-semibold text-red-500">✗ Luar Radius</span>
                        @else
                            <span class="inline-flex rounded-full bg-[#dff5e9] px-2 py-1 text-[9px] font-semibold text-[#348b66]">✓ Terverifikasi</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($isRejected || $outside || $isSickLeave)
                            <p class="text-gray-400">Rp0</p>
                            <p class="text-[9px] {{ $isRejected ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $isRejected ? 'Alpa / Tanpa Keterangan' : ($isSickLeave ? 'Tidak ada gaji' : 'Ditolak lokasi') }}
                            </p>
                        @else
                            <p class="text-gray-800 font-semibold">Rp{{ number_format($att->daily_wage, 0, ',', '.') }}</p>
                            <p class="text-[9px] text-gray-400">Gaji hari ini</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data presensi pada rentang tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection