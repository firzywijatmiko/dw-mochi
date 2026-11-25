@extends('layouts.employee')
@section('content')
<div class="min-h-screen bg-[#f0f2f5] pb-20">
    <!-- Header Sticky Full Width -->
    <header class="bg-white px-5 sm:px-10 md:px-16 py-4 flex items-center border-b border-gray-200 sticky top-0 z-10">
        <a href="{{ route('employee.dashboard') }}" class="text-gray-500 hover:text-[#0f2137] p-2 -ml-2 rounded-lg hover:bg-gray-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div class="ml-2">
            <h1 class="text-base sm:text-lg font-bold text-[#0f2137]">Rekap Riwayat Presensi & Slip Gaji Mingguan</h1>
        </div>
    </header>

    <!-- Konten Full Mentok Kiri-Kanan -->
    <main class="w-full px-5 sm:px-10 md:px-16 pt-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-4">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Daftar Periode Mingguan</h2>
            <p class="text-xs text-gray-400">Klik rincian untuk melihat log presensi harian</p>
        </div>

        <div class="space-y-4">
            @forelse($weeklyPeriods as $index => $week)
            @php $isHighlight = $week['is_current']; @endphp

            <article x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }"
                     class="{{ $isHighlight ? 'bg-[#0f2137] border-[#0f2137] text-white shadow-md' : 'bg-white border-[#e7e0d8] text-[#0f2137] shadow-2xs' }} rounded-2xl border overflow-hidden transition">
                
                <!-- Baris Utama Kartu Minggu -->
                <div class="p-5 sm:px-7 sm:py-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Kiri: Periode, Badge & Ringkasan Hari -->
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h3 class="font-bold {{ $isHighlight ? 'text-white' : 'text-[#0f2137]' }} text-base sm:text-lg">
                                {{ $week['period_label'] }}
                            </h3>

                            @if($isHighlight)
                                <span class="text-[11px] font-bold text-[#0f2137] bg-[#e8d5c4] px-2.5 py-0.5 rounded-md">
                                    Minggu Ini
                                </span>
                            @endif

                            @if($week['is_paid'])
                                <span class="text-[11px] font-semibold {{ $isHighlight ? 'text-emerald-200 bg-emerald-500/20 border-emerald-400/30' : 'text-[#348b66] bg-[#dff5e9] border-[#c3ebd5]' }} border px-2.5 py-0.5 rounded-md">
                                    Sudah Dibayar {{ $week['paid_at'] ? '(' . $week['paid_at']->format('d M Y') . ')' : '' }}
                                </span>
                            @else
                                <span class="text-[11px] font-semibold {{ $isHighlight ? 'text-[#f3c69f] bg-white/10 border-white/15' : 'text-[#c28455] bg-[#fff4e8] border-[#f5e3d3]' }} border px-2.5 py-0.5 rounded-md">
                                    Belum Dibayar
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm {{ $isHighlight ? 'text-gray-300' : 'text-gray-500' }}">
                            <span>Kehadiran: <strong class="{{ $isHighlight ? 'text-white' : 'text-[#0f2137]' }}">{{ $week['days_present'] }} Hari</strong></span>
                            @if($week['sick_days'] > 0)
                                <span>•</span>
                                <span>Izin Sakit: <strong class="{{ $isHighlight ? 'text-[#f3c69f]' : 'text-[#c28455]' }}">{{ $week['sick_days'] }} Hari</strong></span>
                            @endif
                            <span>•</span>
                            <span>Lembur: <strong class="{{ $isHighlight ? 'text-white' : 'text-[#0f2137]' }}">{{ $week['overtime'] }} Jam</strong></span>
                        </div>
                    </div>

                    <!-- Kanan: Nominal + Tombol Aksi -->
                    <div class="flex flex-wrap items-center justify-between lg:justify-end gap-4 border-t lg:border-t-0 pt-3.5 lg:pt-0 {{ $isHighlight ? 'border-white/10' : 'border-gray-100' }}">
                        <div class="lg:text-right">
                            <p class="text-[10px] font-bold uppercase tracking-wider {{ $isHighlight ? 'text-[#e8d5c4]' : 'text-gray-400' }}">
                                {{ $week['is_paid'] ? 'Total Diterima' : 'Estimasi Gaji' }}
                            </p>
                            <p class="text-xl sm:text-2xl font-extrabold {{ $isHighlight ? 'text-white' : 'text-[#0f2137]' }}">
                                Rp {{ number_format($week['total_pay'], 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($week['is_paid'] && $week['payslip'])
                                @if($week['payslip']->isPrintable())
                                    <a href="{{ route('employee.payslips.show', $week['payslip']) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl {{ $isHighlight ? 'bg-[#e8d5c4] hover:bg-[#dec4ad] text-[#0f2137]' : 'bg-[#0f2137] hover:bg-[#1a3556] text-white' }} px-4 py-2.5 text-xs font-bold transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path></svg>
                                        Slip Gaji
                                    </a>
                                @else
                                    <span class="rounded-xl bg-gray-100 border border-gray-200 px-3 py-2 text-xs font-medium text-gray-400">
                                        Masa Cetak Habis
                                    </span>
                                @endif
                            @endif

                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1.5 rounded-xl border {{ $isHighlight ? 'border-white/20 bg-white/10 hover:bg-white/20 text-white' : 'border-gray-200 bg-[#faf9f7] hover:bg-gray-100 text-[#0f2137]' }} px-3.5 py-2.5 text-xs font-bold transition cursor-pointer">
                                <span>Rincian ({{ $week['attendances']->count() }})</span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Tabel Rincian Harian (Tetap Putih Bersih Biar Enak Dibaca) -->
                <div x-show="open" x-transition class="border-t {{ $isHighlight ? 'border-white/10 bg-[#f8f9fb]' : 'border-gray-100 bg-[#faf9f7]' }} px-5 sm:px-7 py-4 text-[#0f2137]">
                    @if($week['attendances']->isNotEmpty())
                        <div class="overflow-x-auto rounded-xl border border-[#e7e0d8] bg-white">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/80 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                        <th class="py-2.5 px-4">Hari & Tanggal</th>
                                        <th class="py-2.5 px-3">Masuk</th>
                                        <th class="py-2.5 px-3">Pulang</th>
                                        <th class="py-2.5 px-3">Durasi & Lembur</th>
                                        <th class="py-2.5 px-4 text-right">Upah Harian</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-xs">
                                    @foreach($week['attendances'] as $att)
                                    <tr class="hover:bg-[#fcfbf9] transition">
                                        <td class="py-3 px-4 font-bold text-[#0f2137] whitespace-nowrap">
                                            {{ $att->date->translatedFormat('l, d M Y') }}
                                            @if($att->attendance_status === 'izin_sakit')
                                                <span class="ml-1.5 inline-block rounded bg-[#fff4e8] border border-[#f5e3d3] px-1.5 py-0.5 text-[10px] font-semibold text-[#c28455]">Izin Sakit</span>
                                            @elseif($att->attendance_status === 'alpa')
                                                <span class="ml-1.5 inline-block rounded bg-red-50 border border-red-200 px-1.5 py-0.5 text-[10px] font-semibold text-red-600">Alpa</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-gray-700 font-medium whitespace-nowrap">
                                            {{ $att->check_in_at ? $att->check_in_at->format('H:i') . ' WIB' : '—' }}
                                        </td>
                                        <td class="py-3 px-3 text-gray-700 font-medium whitespace-nowrap">
                                            {{ $att->check_out_at ? $att->check_out_at->format('H:i') . ' WIB' : '—' }}
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <span class="font-semibold text-[#0f2137]">{{ number_format($att->worked_hours ?? 0, 1, ',', '.') }} jam</span>
                                            @if(($att->overtime_1x + $att->overtime_2x) > 0)
                                                <span class="ml-1 rounded bg-[#fff4e8] px-1.5 py-0.5 text-[10px] font-semibold text-[#c28455]">
                                                    +{{ $att->overtime_1x + $att->overtime_2x }}j lembur
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-[#348b66] whitespace-nowrap">
                                            Rp {{ number_format($att->daily_wage, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-xs text-gray-400 py-3">Tidak ada log presensi harian pada periode ini.</p>
                    @endif
                </div>
            </article>
            @empty
            <div class="text-center bg-white rounded-2xl border border-[#e7e0d8] py-14 text-gray-400 text-sm">
                Belum ada riwayat presensi maupun penggajian.
            </div>
            @endforelse
        </div>
    </main>
</div>
@endsection