<!-- resources/views/owner/payrolls/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Penggajian')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="payrollPage()" class="space-y-4">

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <!-- Ringkasan + filter -->
    <div class="flex flex-col sm:flex-row flex-wrap sm:items-end gap-3">
        <div class="flex-1 bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3 flex items-center gap-3">
            <div class="p-2 bg-[#fff6d8] text-[#a17b1c] rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Total Belum Dibayar</p>
                <p class="text-base font-bold text-gray-800">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap flex-1 gap-3">
            <form method="GET" action="{{ route('owner.payrolls.index') }}" class="flex-1 min-w-[120px]">
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Filter Bulan</label>
                <select name="month" onchange="this.form.submit()" class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none">
                    <option value="">Semua</option>
                    @foreach($monthOptions as $value => $label)
                        <option value="{{ $value }}" {{ $month === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Riwayat Karyawan</label>
                <select x-model="empId" @change="open = empId !== ''" class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none">
                    <option value="">— Pilih karyawan —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Tabel penggajian -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[700px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Gaji Pokok</th>
                    <th class="px-4 py-3">Lembur</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                @forelse($payrolls as $p)
                @php
                    $name = $p->employee->name ?? '-';
                    $paid = $p->status === 'Dibayar';
                @endphp
                <tr class="hover:bg-[#fcfbf9] transition">
                    <td class="px-4 py-3 font-semibold text-[#0f2137]">{{ $name }}</td>
                    <td class="px-4 py-3">{{ $p->period_label }}</td>
                    <td class="px-4 py-3">
                        Rp {{ number_format($p->base_pay, 0, ',', '.') }}
                        @if($p->meal_pay > 0)
                            <p class="text-[9px] text-gray-400">+ mkn Rp{{ number_format($p->meal_pay, 0, ',', '.') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 font-bold text-gray-800">Rp {{ number_format($p->total_pay, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('owner.payrolls.status', $p->id) }}">
                            @csrf @method('PUT')
                            <select name="status" data-current="{{ $p->status }}"
                                onchange="if (confirm('Ubah status gaji {{ addslashes($name) }}?')) { this.form.submit(); } else { this.value = this.dataset.current; }"
                                class="px-2 py-1 text-[9px] font-semibold rounded-full border cursor-pointer focus:outline-none {{ $paid ? 'bg-[#dff5e9] text-[#348b66] border-[#348b66]' : 'bg-[#fff6d8] text-[#a17b1c] border-[#a17b1c]' }}">
                                <option value="Belum Dibayar" {{ !$paid ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="Dibayar" {{ $paid ? 'selected' : '' }}>Sudah Dibayar</option>
                            </select>
                        </form>
                        @if(abs($p->difference) >= 1)
                            <p class="mt-1 text-[9px] text-amber-600">
                                Presensi kini Rp{{ number_format($p->current_total, 0, ',', '.') }}
                                (selisih Rp{{ number_format(abs($p->difference), 0, ',', '.') }})
                            </p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data gaji.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL RIWAYAT GAJI -->
    <div x-cloak x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="open = false; empId = ''" class="bg-white w-full max-w-lg rounded-xl shadow-xl overflow-hidden" x-transition>
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100">
                <h3 class="font-bold text-[#0f2137] text-sm">Riwayat Gaji — <span x-text="names[empId]"></span></h3>
                <button type="button" @click="open = false; empId = ''" class="text-gray-400">✕</button>
            </div>
            
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full text-left text-[10px]">
                    <thead class="uppercase text-[9px] tracking-wide text-gray-400 bg-[#faf9f7] sticky top-0">
                        <tr>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Gapok</th>
                            <th class="px-4 py-3">Lembur</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                        <template x-for="row in (histories[empId] || [])" :key="row.id">
                            <tr>
                                <td class="px-4 py-2" x-text="row.period"></td>
                                <td class="px-4 py-2" x-text="row.base"></td>
                                <td class="px-4 py-2" x-text="row.overtime"></td>
                                <td class="px-4 py-2 font-bold text-gray-800" x-text="row.total"></td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-semibold"
                                          :class="row.paid ? 'bg-[#dff5e9] text-[#348b66]' : 'bg-[#fff6d8] text-[#a17b1c]'"
                                          x-text="row.status"></span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!(histories[empId] || []).length">
                            <tr><td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">Belum ada riwayat gaji.</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function payrollPage() {
        return {
            open: false,
            empId: '',
            histories: @json($histories),
            names: @json($employeeNames),
        }
    }
</script>
@endsection