@extends('layouts.app')

@section('header_title', 'Laporan Keuangan')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="{ openPreviewModal: false }" @keydown.escape.window="openPreviewModal = false" class="space-y-4">

    @php $currentPerPage = $perPage ?? request('per_page', 10); @endphp

    {{-- Filter Periode & Tombol Unduh PDF --}}
    <div class="flex flex-col sm:flex-row flex-wrap sm:items-center justify-between gap-3 rounded-lg border border-[#e7e0d8] bg-white p-3 shadow-sm">
        <form method="GET" action="{{ route('owner.reports.index') }}" class="flex flex-wrap items-center gap-2">
            @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
            <input type="hidden" name="chart_mode" value="{{ $chartMode ?? 'weekly' }}">

            <div class="flex items-center gap-1.5">
                <span class="text-xs text-gray-500">Dari:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 border border-gray-200 rounded-md text-xs text-gray-700 focus:border-[#0f2137] outline-none">
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-gray-500">Sampai:</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 border border-gray-200 rounded-md text-xs text-gray-700 focus:border-[#0f2137] outline-none">
            </div>
            <button type="submit" class="bg-[#0f2137] text-white px-3.5 py-1.5 rounded-md text-xs font-medium hover:bg-[#183252] transition">Tampilkan</button>
        </form>

        <button type="button" @click="openPreviewModal = true" class="inline-flex items-center justify-center gap-1.5 border border-gray-200 bg-white px-3.5 py-1.5 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Unduh PDF
        </button>
    </div>

    <!-- MODAL PREVIEW LAPORAN KEUANGAN -->
    <div x-cloak x-show="openPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="openPreviewModal = false" class="w-full max-w-2xl rounded-xl bg-white p-4 shadow-xl">
            <div class="flex items-center justify-between px-2 pb-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-[#0f2137]">Preview Laporan Keuangan</h3>
                <button type="button" @click="openPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="my-4 max-h-[65vh] overflow-y-auto rounded-lg border border-gray-300 bg-white p-5 text-[#222] shadow-inner">
                <div class="border-b-2 border-[#0f2137] pb-2.5 mb-3">
                    <h1 class="text-base font-bold text-[#0f2137] m-0">Laporan Keuangan DW Mochi</h1>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                        s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>
                </div>

                <table class="w-full border-collapse text-[11px] mb-4">
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 px-2.5 py-1.5 w-[55%]">Total Pemasukan</td>
                            <td class="border border-gray-300 px-2.5 py-1.5 text-right">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-2.5 py-1.5">Pengeluaran Bahan Baku</td>
                            <td class="border border-gray-300 px-2.5 py-1.5 text-right">Rp {{ number_format($materialExpense ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-2.5 py-1.5">Pengeluaran Gaji Karyawan</td>
                            <td class="border border-gray-300 px-2.5 py-1.5 text-right">Rp {{ number_format($wageExpense ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 px-2.5 py-1.5">Total Pengeluaran</td>
                            <td class="border border-gray-300 px-2.5 py-1.5 text-right">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="font-bold bg-gray-50">
                            <td class="border border-gray-300 px-2.5 py-1.5">Keuntungan Bersih</td>
                            <td class="border border-gray-300 px-2.5 py-1.5 text-right" style="{{ ($netProfit ?? 0) >= 0 ? 'color: #348b66;' : 'color: #bd4d48;' }}">
                                Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h4 class="text-xs font-bold text-[#0f2137] mt-4 mb-1.5">Rincian Harian</h4>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-[10px]">
                        <thead>
                            <tr class="bg-gray-100 uppercase text-gray-600">
                                <th class="border border-gray-300 px-2 py-1.5 text-left">Tanggal</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Pesanan</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Pendapatan</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Bahan Baku</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Gaji</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($allDailyReport ?? $dailyReport) as $row)
                            <tr>
                                <td class="border border-gray-300 px-2 py-1.5 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right">{{ $row['orders'] }}</td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right whitespace-nowrap">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right whitespace-nowrap">Rp {{ number_format($row['material'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right whitespace-nowrap">Rp {{ number_format($row['wage'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right whitespace-nowrap font-semibold" style="{{ $row['profit'] >= 0 ? 'color: #348b66;' : 'color: #bd4d48;' }}">
                                    Rp {{ number_format($row['profit'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-2 py-3 text-center text-gray-400">Tidak ada transaksi harian pada periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($mergedExpenses))
                    <h4 class="text-xs font-bold text-[#0f2137] mt-4 mb-1.5">Rincian Pengeluaran</h4>
                    <table class="w-full border-collapse text-[10px]">
                        <thead>
                            <tr class="bg-gray-100 uppercase text-gray-600">
                                <th class="border border-gray-300 px-2 py-1.5 text-left">Tanggal</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-left">Nama Item / Keterangan</th>
                                <th class="border border-gray-300 px-2 py-1.5 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mergedExpenses as $item)
                            <tr>
                                <td class="border border-gray-300 px-2 py-1.5 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->expense_date)->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 px-2 py-1.5">
                                    <strong>{{ $item->item_name }}</strong><br>
                                    <span class="text-[9px] text-gray-500">{{ $item->notes ?? '-' }}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1.5 text-right whitespace-nowrap">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="border border-gray-300 px-2 py-3 text-center text-gray-400">Tidak ada catatan pengeluaran pada periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif

                <p class="mt-3 text-[9px] text-gray-400">
                    Dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} oleh Sistem Manajemen DW Mochi
                </p>
            </div>

            <div class="mt-4 flex gap-2 px-2">
                <a href="{{ route('owner.reports.index', ['start_date' => $startDate, 'end_date' => $endDate, 'download' => 1]) }}" 
                   @click="openPreviewModal = false"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-md bg-[#0f2137] px-4 py-2 text-xs font-medium text-white hover:bg-[#183252] transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh PDF
                </a>
                <button type="button" @click="openPreviewModal = false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Card Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-[#0f2137] text-white p-4 rounded-lg shadow-sm">
            <p class="text-[10px] text-gray-300 uppercase tracking-wider mb-0.5">Total Pemasukan</p>
            <h3 class="text-base font-bold">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">Dari pesanan selesai</p>
        </div>
        <div class="bg-white text-gray-800 p-4 rounded-lg shadow-sm border border-[#e7e0d8]">
            <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Total Pengeluaran</p>
            <h3 class="text-base font-bold">Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">Bahan baku (Rp {{ number_format($materialExpense ?? 0, 0, ',', '.') }}) + Gaji (Rp {{ number_format($wageExpense ?? 0, 0, ',', '.') }})</p>
        </div>
        <div class="bg-white text-gray-800 p-4 rounded-lg shadow-sm border border-[#e7e0d8]">
            <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Keuntungan Bersih</p>
            <h3 class="text-base font-bold {{ ($netProfit ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}
            </h3>
            <p class="text-[10px] text-gray-400 mt-1">Pemasukan dikurangi pengeluaran</p>
        </div>
    </div>

    <!-- Grafik Tren Keuangan -->
    @php
        $short = function ($n) {
            if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 2, ',', '.'), '0'), ',') . 'jt';
            if ($n > 0) return rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'rb';
            return '0';
        };
        $chartList    = $chartData ?? [];
        $totalBars    = count($chartList);
        $isDailyMode  = ($chartMode ?? 'weekly') === 'daily';
        $hasChartData = collect($chartList)->contains(fn($w) => $w['income'] > 0 || $w['expense'] > 0);
    @endphp
    <div x-data="{ hoveredBar: null }" class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="font-bold text-[#0f2137] text-sm">
                    Tren Keuangan {{ $isDailyMode ? 'Harian' : (($chartMode ?? 'weekly') === 'monthly' ? 'Bulanan' : 'Mingguan') }}
                </h3>
                <p class="text-[11px] text-gray-400">
                    Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                </p>
            </div>

            {{-- Tab Switcher Harian / Mingguan / Bulanan --}}
            <div class="inline-flex rounded-lg border border-gray-200 bg-[#faf9f7] p-0.5 text-xs">
                @foreach(['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $modeKey => $modeLabel)
                    <a href="{{ route('owner.reports.index', array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate, 'chart_mode' => $modeKey])) }}"
                       class="px-3 py-1 rounded-md font-medium transition {{ ($chartMode ?? 'weekly') === $modeKey ? 'bg-[#0f2137] text-white shadow-sm' : 'text-gray-600 hover:text-[#0f2137]' }}">
                        {{ $modeLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Info Bar Hover --}}
        <div class="mb-3 flex min-h-[28px] flex-wrap items-center justify-between gap-2 rounded-md border border-[#eee9e4] bg-[#faf9f7] px-3 py-1.5 text-[11px]">
            <template x-if="hoveredBar">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="font-bold text-[#0f2137]" x-text="hoveredBar.label"></span>
                    <span class="text-gray-600">Pemasukan: <strong class="text-[#0f2137]" x-text="'Rp ' + hoveredBar.income"></strong></span>
                    <span class="text-gray-600">Pengeluaran: <strong class="text-[#8b5a33]" x-text="'Rp ' + hoveredBar.expense"></strong></span>
                </div>
            </template>
            <template x-if="!hoveredBar">
                <span class="text-gray-400">Arahkan kursor ke batang grafik untuk melihat rincian nominal per {{ $isDailyMode ? 'tanggal' : (($chartMode ?? 'weekly') === 'monthly' ? 'bulan' : 'minggu') }}.</span>
            </template>
        </div>

        <div class="w-full flex">
            {{-- Sumbu Y Tetap di Kiri --}}
            <div class="flex flex-col justify-between text-[10px] text-gray-400 pr-2 h-44 text-right select-none w-11 shrink-0 pb-8">
                <span>{{ isset($chartMax) ? $short($chartMax) : '0' }}</span>
                <span>{{ isset($chartMax) ? $short($chartMax * 0.5) : '0' }}</span>
                <span>0</span>
            </div>

            {{-- Area Batang & Label Sumbu X --}}
            <div class="flex-1 overflow-x-auto pb-1">
                <div class="{{ $totalBars > 10 ? 'min-w-[960px]' : 'w-full' }}">
                    <div class="relative h-44 border-b border-l border-gray-200">
                        <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-gray-200/80 pointer-events-none"></div>

                        <div class="relative h-full flex items-end justify-around">
                            @foreach($chartList as $idx => $w)
                                @php
                                    $incomePct   = (isset($chartMax) && $chartMax > 0) ? ($w['income']  / $chartMax * 100) : 0;
                                    $expensePct  = (isset($chartMax) && $chartMax > 0) ? ($w['expense'] / $chartMax * 100) : 0;
                                    $isNewMonth  = $isDailyMode && $idx > 0 && str_starts_with(trim($w['label']), '1 ');
                                @endphp
                                <div @mouseenter="hoveredBar = { label: @js($w['label']), income: @js(number_format($w['income'], 0, ',', '.')), expense: @js(number_format($w['expense'], 0, ',', '.')) }"
                                     @mouseleave="hoveredBar = null"
                                     class="flex-1 h-full flex items-end justify-center gap-0.5 px-1 z-10 hover:bg-[#faf8f5] transition cursor-pointer border-l {{ $isNewMonth ? 'border-l-2 border-gray-300' : 'border-gray-100 first:border-l-0' }}">
                                    <div class="w-full max-w-[16px] bg-[#0f2137] rounded-t transition-all"
                                         style="height: {{ $w['income'] > 0 ? max($incomePct, 3) : 1 }}%; opacity: {{ $w['income'] > 0 ? '1' : '0.2' }};"></div>
                                    <div class="w-full max-w-[16px] bg-[#8b5a33] rounded-t transition-all"
                                         style="height: {{ $w['expense'] > 0 ? max($expensePct, 3) : 1 }}%; opacity: {{ $w['expense'] > 0 ? '1' : '0.2' }};"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Label Sumbu X: Tanggal + Bulan bertumpuk 2 baris agar jelas & tidak bertabrakan --}}
                    <div class="flex justify-around mt-1.5">
                        @foreach($chartList as $idx => $w)
                            @php
                                $parts      = explode(' ', trim($w['label']), 2);
                                $isNewMonth = $isDailyMode && $idx > 0 && ($parts[0] ?? '') === '1';
                            @endphp
                            <div class="flex-1 text-center px-0.5 leading-tight {{ $isNewMonth ? 'border-l-2 border-gray-300' : '' }}" title="{{ $w['label'] }}">
                                @if($isDailyMode && count($parts) === 2)
                                    <div class="text-[10px] font-bold {{ $isNewMonth ? 'text-[#0f2137]' : 'text-gray-700' }}">{{ $parts[0] }}</div>
                                    <div class="text-[9px] {{ $isNewMonth ? 'font-semibold text-[#c28455]' : 'text-gray-400' }}">{{ $parts[1] }}</div>
                                @else
                                    <span class="text-[10px] font-medium text-gray-600 whitespace-nowrap">{{ $w['label'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @unless($hasChartData)
            <p class="text-center text-xs text-gray-400 mt-2">Belum ada data transaksi pada periode ini.</p>
        @endunless
        <div class="flex justify-center items-center space-x-5 mt-3">
            <div class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 bg-[#0f2137] rounded-sm"></span><span class="text-xs text-gray-600">Pemasukan</span></div>
            <div class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 bg-[#8b5a33] rounded-sm"></span><span class="text-xs text-gray-600">Pengeluaran</span></div>
        </div>
    </div>

    <!-- Card Gabungan Tabel Rincian Arus Kas Harian & Pagination -->
    <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] overflow-hidden">
        <div class="px-4 py-3 border-b border-[#e7e0d8] bg-[#faf9f7]">
            <h3 class="font-bold text-[#0f2137] text-sm">Rincian Arus Kas Harian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[650px] w-full text-left text-xs">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] uppercase text-[10px] font-semibold tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pesanan Selesai</th>
                        <th class="px-4 py-3">Pendapatan</th>
                        <th class="px-4 py-3">Bahan Baku</th>
                        <th class="px-4 py-3">Gaji Dibayarkan</th>
                        <th class="px-4 py-3">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-700">
                    @forelse($dailyReport as $row)
                    <tr class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-3 font-medium">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $row['orders'] }} pesanan</td>
                        <td class="px-4 py-3 text-emerald-700">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-red-600">Rp {{ number_format($row['material'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-red-600">Rp {{ number_format($row['wage'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-bold {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            Rp {{ number_format($row['profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-xs text-gray-400">Tidak ada transaksi harian pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        @if(method_exists($dailyReport, 'total'))
            <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('owner.reports.index') }}" method="GET" class="flex items-center gap-1.5">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        <input type="hidden" name="chart_mode" value="{{ $chartMode ?? 'weekly' }}">
                        <select name="per_page" onchange="this.form.submit()" class="rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 focus:border-[#0f2137] focus:outline-none">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="text-gray-400">per halaman</span>
                    </form>

                    <span class="hidden sm:inline text-gray-300">|</span>

                    <span class="text-gray-500">
                        Menampilkan <strong class="text-gray-700">{{ $dailyReport->firstItem() ?? 0 }} - {{ $dailyReport->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $dailyReport->total() }}</strong> data
                    </span>
                </div>

                @if($dailyReport->hasPages())
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            @if($dailyReport->onFirstPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $dailyReport->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $current = $dailyReport->currentPage();
                                $last    = $dailyReport->lastPage();
                                $start   = max(1, $current - 2);
                                $end     = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $dailyReport->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                                @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $dailyReport->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                                <a href="{{ $dailyReport->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                            @endif

                            @if($dailyReport->hasMorePages())
                                <a href="{{ $dailyReport->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('owner.reports.index') }}" method="GET" class="flex items-center gap-1.5">
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                            <input type="hidden" name="chart_mode" value="{{ $chartMode ?? 'weekly' }}">
                            <span class="text-gray-400">Ke hal:</span>
                            <input type="number" name="page" min="1" max="{{ $dailyReport->lastPage() }}" placeholder="{{ $dailyReport->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection