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
            border: 0 !important;
            box-shadow: none !important;
        }
        #payslip-document * { color: #111827 !important; }
    }
</style>

<div class="min-h-screen bg-[#f3f5f7] px-4 py-5 sm:px-6 sm:py-8">
    <main class="mx-auto max-w-xl">
        <div class="print-hidden mb-4 flex items-center justify-between">
            <a href="{{ route('employee.payslips') }}"
               class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-bold text-gray-600 transition hover:bg-white hover:text-[#0f2137]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#dce4eb] bg-white px-3.5 py-2.5 text-xs font-bold text-[#0f2137] shadow-sm transition hover:border-[#bdcbd7] hover:bg-[#f7f9fb]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z"></path>
                </svg>
                Cetak slip
            </button>
        </div>

        <article id="payslip-document" class="overflow-hidden rounded-2xl border border-[#e2e6ea] bg-white shadow-[0_8px_24px_rgba(15,33,55,0.06)]">
            <header class="border-b border-gray-100 px-5 py-5 sm:px-7">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-400">Slip gaji karyawan</p>
                <h1 class="mt-1 text-xl font-bold text-[#0f2137]">DW Mochi</h1>
                <div class="mt-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-800">{{ $payslip->employee->name }}</p>
                        <p class="mt-1 text-xs text-gray-500">Karyawan</p>
                    </div>
                    <p class="text-xs font-medium text-gray-500">
                        {{ $payslip->period_start->format('d M Y') }} - {{ $payslip->period_end->format('d M Y') }}
                    </p>
                </div>
            </header>

            <div class="px-5 py-5 sm:px-7">
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500">Hari hadir</dt>
                        <dd class="font-bold text-gray-800">{{ $payslip->days_present }} hari</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500">Gaji pokok</dt>
                        <dd class="font-bold text-gray-800">Rp{{ number_format($payslip->base_pay, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500">Uang makan</dt>
                        <dd class="font-bold text-gray-800">Rp{{ number_format($payslip->meal_pay, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500">Lembur</dt>
                        <dd class="font-bold text-gray-800">Rp{{ number_format($payslip->overtime_pay, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-gray-500">Uang sakit</dt>
                        <dd class="font-bold text-gray-800">Rp{{ number_format($payslip->sick_pay, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-center justify-between rounded-xl bg-[#f2f5f8] px-4 py-4">
                    <span class="text-sm font-bold text-[#0f2137]">Total diterima</span>
                    <span class="text-lg font-bold text-[#0f2137]">Rp{{ number_format($payslip->total_pay, 0, ',', '.') }}</span>
                </div>

                <p class="mt-4 text-right text-[11px] text-gray-400">
                    Dibayar {{ $payslip->paid_at->format('d M Y, H:i') }} WIB
                </p>
            </div>
        </article>
    </main>
</div>
@endsection