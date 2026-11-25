<!-- resources/views/owner/attendances/recap.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="attendanceRecap()" @keydown.escape.window="proofModal.open = false" class="space-y-4">
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

    <!-- Filter rentang tanggal & karyawan + Kartu Statistik (1 Baris Sejajar, Gaya Penggajian) -->
    <div class="flex flex-col xl:flex-row flex-wrap items-stretch gap-3">
        {{-- Card Filter --}}
        <form method="GET" action="{{ route('owner.attendances.recap') }}" class="flex-[1.5] bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-3.5 flex flex-wrap items-end gap-3">
            @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
            
            <div class="flex-1 min-w-[130px]">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                       class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs text-gray-700 focus:border-[#0f2137] outline-none">
            </div>

            <div class="flex-1 min-w-[130px]">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                       class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs text-gray-700 focus:border-[#0f2137] outline-none">
            </div>

            {{-- Searchable Dropdown Karyawan --}}
            <div class="flex-[1.3] min-w-[180px] relative" @click.away="empDropdownOpen = false">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Karyawan</label>
                <input type="hidden" name="employee_id" :value="selectedEmpId">

                <button type="button"
                        @click="empDropdownOpen = !empDropdownOpen; if(empDropdownOpen) $nextTick(() => $refs.empSearchInput.focus())"
                        class="w-full flex items-center justify-between gap-2 px-3 py-1.5 border border-gray-200 rounded-md bg-white text-xs text-gray-700 hover:border-gray-300 focus:border-[#0f2137] outline-none">
                    <span class="truncate" x-text="selectedEmpName"></span>
                    <svg class="h-3.5 w-3.5 text-gray-400 shrink-0 transition-transform" :class="empDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="empDropdownOpen" x-cloak
                     class="absolute left-0 right-0 z-30 mt-1 rounded-lg border border-gray-200 bg-white shadow-lg overflow-hidden">
                    <div class="p-2 border-b border-gray-100 bg-[#faf9f7]">
                        <input type="text"
                               x-ref="empSearchInput"
                               x-model="empSearch"
                               placeholder="Cari nama karyawan..."
                               class="w-full rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-700 placeholder-gray-400 focus:border-[#0f2137] outline-none">
                    </div>
                    <ul class="max-h-52 overflow-y-auto divide-y divide-gray-50 text-xs">
                        <li>
                            <button type="button"
                                    @click="selectEmployee('', 'Semua karyawan')"
                                    :class="selectedEmpId === '' ? 'bg-[#0f2137] text-white font-semibold' : 'text-gray-700 hover:bg-gray-50'"
                                    class="w-full text-left px-3 py-2 transition">
                                Semua karyawan
                            </button>
                        </li>
                        <template x-for="emp in filteredEmployees" :key="emp.id">
                            <li>
                                <button type="button"
                                        @click="selectEmployee(String(emp.id), emp.name)"
                                        :class="selectedEmpId === String(emp.id) ? 'bg-[#0f2137] text-white font-semibold' : 'text-gray-700 hover:bg-gray-50'"
                                        class="w-full text-left px-3 py-2 transition truncate"
                                        x-text="emp.name">
                                </button>
                            </li>
                        </template>
                        <li x-show="filteredEmployees.length === 0" class="px-3 py-3 text-center text-[11px] text-gray-400">
                            Nama karyawan tidak ditemukan.
                        </li>
                    </ul>
                </div>
            </div>

            <button type="submit" class="rounded-md bg-[#0f2137] px-4 py-1.5 text-xs font-medium text-white hover:bg-[#183252] transition h-[30px]">
                Tampilkan
            </button>
        </form>

        {{-- 3 Kartu Statistik Presensi (Persis Gaya Penggajian, Letak Tetap di Samping Filter) --}}
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3 flex items-center gap-3">
                <div class="p-2.5 bg-[#edf8f2] text-[#348b66] rounded-lg shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5 truncate">
                        Hadir
                    </p>
                    <p class="text-base font-bold text-gray-800">
                        {{ $summary['present'] }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3 flex items-center gap-3">
                <div class="p-2.5 bg-[#fff6d8] text-[#a17b1c] rounded-lg shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5 truncate">
                        Izin Sakit
                    </p>
                    <p class="text-base font-bold text-gray-800">
                        {{ $summary['sick_leave'] }}
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3 flex items-center gap-3">
                <div class="p-2.5 bg-red-50 text-red-500 rounded-lg shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5 truncate">
                        Alpa
                    </p>
                    <p class="text-base font-bold text-gray-800">
                        {{ $summary['absent'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Gabungan Tabel Presensi & Pagination -->
    <div class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[820px] w-full text-left text-xs">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] uppercase text-[10px] font-semibold tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Nama Karyawan</th>
                        <th class="px-4 py-3">Jam Masuk</th>
                        <th class="px-4 py-3">Jam Pulang</th>
                        <th class="px-4 py-3">Jam Lembur</th>
                        <th class="px-4 py-3">Durasi Kerja</th>
                        <th class="px-4 py-3">Gaji Harian</th>
                        <th class="px-4 py-3">Status Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-700">
                    @forelse($attendances as $att)
                    @php
                        $name        = $att->employee->name ?? '-';
                        $initials    = collect(explode(' ', $name))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        $overtime    = $att->overtime_1x + $att->overtime_2x;
                        $outside     = ($att->location_status ?? 'Terverifikasi') === 'Luar Radius';
                        $isSickLeave = $att->attendance_status === 'izin_sakit';
                        $isRejected  = $att->attendance_status === 'alpa';
                        $proofUrl    = $att->sick_leave_proof ? asset('storage/' . $att->sick_leave_proof) : '';
                    @endphp
                    <tr class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $att->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 text-[10px] font-semibold flex items-center justify-center shrink-0">{{ $initials }}</span>
                                <span class="text-gray-800 font-medium">{{ $name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $isSickLeave ? 'Izin sakit' : (optional($att->check_in_at)->format('H:i') ?? '-') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ optional($att->check_out_at)->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $overtime > 0 ? rtrim(rtrim(number_format($overtime, 2, ',', '.'), '0'), ',') . ' jam' : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $isSickLeave ? '-' : number_format($att->worked_hours ?? 0, 2, ',', '.') . ' jam' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($isRejected || $outside || $isSickLeave)
                                <p class="text-gray-400 font-medium">Rp0</p>
                                <p class="text-[10px] {{ $isRejected ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ $isRejected ? 'Alpa / Tanpa Keterangan' : ($isSickLeave ? 'Tidak ada gaji' : 'Ditolak lokasi') }}
                                </p>
                            @else
                                <p class="text-gray-800 font-semibold">Rp{{ number_format($att->daily_wage, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-gray-400">Gaji hari ini</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($isSickLeave || $isRejected)
                                <div class="flex flex-col items-start gap-1.5">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="inline-flex rounded-md border {{ $isRejected ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200' }} px-2 py-0.5 text-[10px] font-medium whitespace-nowrap">
                                            {{ $isRejected ? 'Alpa / Tanpa Keterangan' : 'Izin sakit' }}
                                        </span>

                                        @if($isSickLeave && $att->sick_leave_status !== 'pending')
                                            <span class="inline-flex rounded-md border {{ $att->sick_leave_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }} px-2 py-0.5 text-[10px] font-medium whitespace-nowrap">
                                                {{ $att->sick_leave_status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($att->sick_leave_proof)
                                            <button type="button"
                                                    @click="openProofModal({
                                                        name: @js($name),
                                                        date: @js($att->date->translatedFormat('d M Y')),
                                                        url: @js($proofUrl),
                                                        isPending: @json($isSickLeave && $att->sick_leave_status === 'pending'),
                                                        statusLabel: @js($att->sick_leave_status === 'approved' ? 'Disetujui' : ($att->sick_leave_status === 'rejected' ? 'Ditolak' : 'Menunggu Verifikasi')),
                                                        approveUrl: @js(route('owner.attendances.approve_sick_leave', $att)),
                                                        rejectUrl: @js(route('owner.attendances.reject_sick_leave', $att))
                                                    })"
                                                    class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-[10px] font-medium text-gray-700 hover:bg-gray-50 transition whitespace-nowrap">
                                                <svg class="h-3 w-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Lihat Bukti
                                            </button>
                                        @endif

                                        @if($isSickLeave && $att->sick_leave_status === 'pending')
                                            <form method="POST" action="{{ route('owner.attendances.approve_sick_leave', $att) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-medium text-emerald-700 hover:bg-emerald-100 transition">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('owner.attendances.reject_sick_leave', $att) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[10px] font-medium text-red-600 hover:bg-red-100 transition">Tolak</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @elseif($outside)
                                <span class="inline-flex rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700 whitespace-nowrap">
                                    Luar Radius
                                </span>
                            @else
                                <span class="inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 whitespace-nowrap">
                                    Terverifikasi
                                </span>
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

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        @if(method_exists($attendances, 'total'))
            @php $currentPerPage = $perPage ?? request('per_page', 10); @endphp
            <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('owner.attendances.recap') }}" method="GET" class="flex items-center gap-1.5">
                        <input type="hidden" name="start_date" value="{{ $startDate->toDateString() }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->toDateString() }}">
                        @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                        <select name="per_page" onchange="this.form.submit()" class="rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 focus:border-[#0f2137] focus:outline-none">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="text-gray-400">per halaman</span>
                    </form>

                    <span class="hidden sm:inline text-gray-300">|</span>

                    <span class="text-gray-500">
                        Menampilkan <strong class="text-gray-700">{{ $attendances->firstItem() ?? 0 }} - {{ $attendances->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $attendances->total() }}</strong> data
                    </span>
                </div>

                @if($attendances->hasPages())
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            @if($attendances->onFirstPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $attendances->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $current = $attendances->currentPage();
                                $last    = $attendances->lastPage();
                                $start   = max(1, $current - 2);
                                $end     = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $attendances->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                                @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $attendances->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                                <a href="{{ $attendances->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                            @endif

                            @if($attendances->hasMorePages())
                                <a href="{{ $attendances->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('owner.attendances.recap') }}" method="GET" class="flex items-center gap-1.5">
                            <input type="hidden" name="start_date" value="{{ $startDate->toDateString() }}">
                            <input type="hidden" name="end_date" value="{{ $endDate->toDateString() }}">
                            @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                            <span class="text-gray-400">Ke hal:</span>
                            <input type="number" name="page" min="1" max="{{ $attendances->lastPage() }}" placeholder="{{ $attendances->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- MODAL PREVIEW BUKTI IZIN SAKIT (Terpusat di Layar) -->
    <div x-show="proofModal.open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/50 p-4 backdrop-blur-sm"
         @click.self="proofModal.open = false">
        <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl border border-gray-100">
            {{-- Header Modal --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                <div>
                    <h3 class="text-sm font-bold text-[#0f2137]">Bukti Izin Sakit</h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        <span class="font-medium text-gray-700" x-text="proofModal.name"></span>
                        <span class="mx-1 text-gray-300">•</span>
                        <span x-text="proofModal.date"></span>
                    </p>
                </div>
                <button type="button" @click="proofModal.open = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Area Preview Foto --}}
            <div class="bg-[#faf9f7] p-5 flex items-center justify-center">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm">
                    <img :src="proofModal.url"
                         :alt="'Bukti sakit ' + proofModal.name"
                         class="max-h-[58vh] w-auto rounded object-contain">
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 bg-white px-5 py-3">
                <a :href="proofModal.url" target="_blank"
                   class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                    Buka ukuran penuh
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <div class="flex items-center gap-2">
                    <template x-if="proofModal.isPending">
                        <div class="flex items-center gap-2">
                            <form method="POST" :action="proofModal.approveUrl">
                                @csrf
                                <button type="submit" class="rounded-md bg-[#348b66] px-3.5 py-1.5 text-xs font-medium text-white hover:bg-[#287252] transition">Setujui</button>
                            </form>
                            <form method="POST" :action="proofModal.rejectUrl">
                                @csrf
                                <button type="submit" class="rounded-md bg-red-600 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition">Tolak</button>
                            </form>
                        </div>
                    </template>
                    <button type="button" @click="proofModal.open = false" class="rounded-md border border-gray-200 px-3.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function attendanceRecap() {
    const allEmployees = @json($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name])->values());
    const initialId = @json((string) request('employee_id', ''));
    const initialEmp = allEmployees.find(e => String(e.id) === initialId);

    return {
        employees: allEmployees,
        empDropdownOpen: false,
        empSearch: '',
        selectedEmpId: initialId,
        selectedEmpName: initialEmp ? initialEmp.name : 'Semua karyawan',

        proofModal: {
            open: false,
            name: '',
            date: '',
            url: '',
            isPending: false,
            statusLabel: '',
            approveUrl: '',
            rejectUrl: ''
        },

        get filteredEmployees() {
            if (!this.empSearch.trim()) return this.employees;
            const q = this.empSearch.toLowerCase().trim();
            return this.employees.filter(e => e.name.toLowerCase().includes(q));
        },

        selectEmployee(id, name) {
            this.selectedEmpId = id;
            this.selectedEmpName = name;
            this.empDropdownOpen = false;
            this.empSearch = '';
        },

        openProofModal(data) {
            this.proofModal = { open: true, ...data };
        }
    }
}
</script>
@endsection