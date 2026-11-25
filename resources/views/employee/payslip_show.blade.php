@extends('layouts.employee')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .print-hidden { display: none !important; }
        #payslip-document {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="min-h-screen bg-[#f0f2f5] px-4 py-6 sm:px-6 sm:py-10">
    <main class="mx-auto max-w-2xl">
        <!-- Bar Navigasi Atas -->
        <div class="print-hidden mb-5 flex items-center justify-between">
            <a href="{{ route('employee.salary_history') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-bold text-[#0f2137] shadow-2xs transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Rekap
            </a>

            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#0f2137] px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-[#1a3556] cursor-pointer">
                <svg class="h-4 w-4 text-[#e8d5c4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z"></path>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>

        <!-- Dokumen Slip Gaji -->
        <article id="payslip-document" class="overflow-hidden rounded-3xl border border-[#e4dfd9] bg-white shadow-md">
            <!-- Header Dokumen Navy -->
            <header class="bg-gradient-to-r from-[#0f2137] to-[#1a3556] px-6 py-6 sm:px-8 sm:py-7 text-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e8d5c4]">Slip Gaji Resmi</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-white">DW Mochi</h1>
                        <p class="mt-0.5 text-xs text-gray-300">Sistem Penggajian & Presensi Produksi</p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 border border-emerald-400/30 px-3 py-1 text-[11px] font-bold text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            LUNAS DIBAYAR
                        </span>
                        <p class="mt-2 text-xs font-semibold text-gray-300">
                            No. Slip: <span class="text-white font-bold">#SLP-{{ str_pad($payslip->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Info Karyawan & Periode -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b border-gray-100 bg-[#faf9f7] px-6 py-5 sm:px-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Penerima</p>
                    <p class="mt-1 text-base font-extrabold text-[#0f2137]">{{ $payslip->employee->name }}</p>
                    <p class="text-xs font-medium text-gray-500">Karyawan Produksi</p>
                </div>

                <div class="sm:text-right">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Periode Penggajian</p>
                    <p class="mt-1 text-sm font-bold text-[#0f2137]">
                        {{ $payslip->period_start->translatedFormat('d M Y') }} – {{ $payslip->period_end->translatedFormat('d M Y') }}
                    </p>
                    <p class="mt-0.5 text-xs font-medium text-gray-500">
                        Total Kehadiran: <strong class="text-[#0f2137]">{{ $payslip->days_present }} Hari</strong>
                    </p>
                </div>
            </div>

            <!-- Rincian Komponen Pendapatan -->
            <div class="px-6 py-6 sm:px-8">
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 mb-3">Rincian Pendapatan</p>

                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="flex items-center justify-between py-3.5">
                        <dt class="flex items-center gap-2.5 text-gray-600 font-medium">
                            <span class="h-2 w-2 rounded-full bg-[#0f2137]"></span>
                            Gaji Pokok ({{ $payslip->days_present }} hari)
                        </dt>
                        <dd class="font-bold text-[#0f2137]">Rp {{ number_format($payslip->base_pay, 0, ',', '.') }}</dd>
                    </div>

                    <div class="flex items-center justify-between py-3.5">
                        <dt class="flex items-center gap-2.5 text-gray-600 font-medium">
                            <span class="h-2 w-2 rounded-full bg-[#4c83b5]"></span>
                            Uang Makan
                        </dt>
                        <dd class="font-bold text-[#0f2137]">Rp {{ number_format($payslip->meal_pay, 0, ',', '.') }}</dd>
                    </div>

                    <div class="flex items-center justify-between py-3.5">
                        <dt class="flex items-center gap-2.5 text-gray-600 font-medium">
                            <span class="h-2 w-2 rounded-full bg-[#c28455]"></span>
                            Upah Lembur
                        </dt>
                        <dd class="font-bold text-[#0f2137]">Rp {{ number_format($payslip->overtime_pay, 0, ',', '.') }}</dd>
                    </div>

                    <div class="flex items-center justify-between py-3.5">
                        <dt class="flex items-center gap-2.5 text-gray-600 font-medium">
                            <span class="h-2 w-2 rounded-full bg-[#348b66]"></span>
                            Tunjangan Izin Sakit
                        </dt>
                        <dd class="font-bold text-[#0f2137]">Rp {{ number_format($payslip->sick_pay, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <!-- Highlight Total Diterima -->
                <div class="mt-6 rounded-2xl bg-[#0f2137] p-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#e8d5c4]">Total Diterima (Take Home Pay)</p>
                        <p class="text-[11px] text-gray-300 mt-0.5">Dibayarkan pada {{ $payslip->paid_at->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                        Rp {{ number_format($payslip->total_pay, 0, ',', '.') }}
                    </p>
                </div>

                <p class="mt-5 text-center text-[11px] text-gray-400">
                    Dokumen ini diterbitkan secara otomatis oleh sistem DW Mochi dan sah tanpa tanda tangan basah.
                </p>
            </div>
        </article>
    </main>
</div>
@endsection