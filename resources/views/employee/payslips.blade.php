@extends('layouts.employee')

@section('content')
<div class="min-h-screen bg-[#f3f5f7]">
    <header class="sticky top-0 z-10 border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-4 sm:px-6">
            <a href="{{ route('employee.dashboard') }}"
               class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-gray-300 hover:bg-gray-50 hover:text-[#0f2137]"
               aria-label="Kembali ke dashboard">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-400">Penghasilan</p>
                <h1 class="text-base font-bold text-[#0f2137]">Riwayat Slip Gaji</h1>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-8">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-3">
            @forelse ($payslips as $p)
                <article class="rounded-2xl border border-[#e4e8ec] bg-white p-4 shadow-[0_1px_2px_rgba(15,33,55,0.04)] sm:p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Periode gaji</p>
                            <h2 class="mt-1 text-sm font-bold text-[#0f2137] sm:text-base">
                                {{ $p->period_start->format('d M Y') }} - {{ $p->period_end->format('d M Y') }}
                            </h2>
                            <p class="mt-1.5 text-xs text-gray-500">
                                Dibayar {{ $p->paid_at->format('d M Y') }}
                            </p>
                        </div>

                        @if ($p->isPrintable())
                            <span class="shrink-0 rounded-full bg-[#edf8f2] px-2.5 py-1 text-[10px] font-bold text-[#34795b]">
                                Dibayar
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold text-gray-500">
                                Masa cetak habis
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex items-end justify-between gap-4 border-t border-gray-100 pt-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Total diterima</p>
                            <p class="mt-1 text-xl font-bold text-[#0f2137]">
                                Rp{{ number_format($p->total_pay, 0, ',', '.') }}
                            </p>
                        </div>

                        @if ($p->isPrintable())
                            <a href="{{ route('employee.payslips.show', $p) }}"
                               class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-[#dce4eb] bg-[#f6f8fa] px-3.5 py-2.5 text-xs font-bold text-[#0f2137] transition hover:border-[#bdcbd7] hover:bg-[#edf3f7]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Lihat slip
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-500">Belum ada slip gaji.</p>
                </div>
            @endforelse
        </div>
    </main>
</div>
@endsection