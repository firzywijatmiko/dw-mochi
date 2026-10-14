<!-- resources/views/owner/payrolls/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Penggajian')

@section('content')
<div x-data="payrollPage()" class="space-y-6">

    <!-- Flash Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ $errors->first() }}</div>
    @endif

    <!-- Ringkasan + filter -->
    <div class="flex flex-wrap items-end gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 flex items-center gap-4">
            <div class="p-3 bg-orange-50 text-[#c28455] rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-[#c28455] uppercase tracking-wider mb-0.5">Total Belum Dibayar</p>
                <p class="text-lg font-bold text-gray-800">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('owner.payrolls.index') }}">
            <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Filter Bulan</label>
            <select name="month" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none min-w-[140px]">
                <option value="">Semua</option>
                @foreach($monthOptions as $value => $label)
                    <option value="{{ $value }}" {{ $month === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <div>
            <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Riwayat Karyawan</label>
            <select x-model="empId" @change="open = empId !== ''" class="px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none min-w-[180px]">
                <option value="">— Pilih karyawan —</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabel penggajian -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 uppercase font-semibold text-[10px] text-[#c28455]">
                <tr>
                    <th class="px-6 py-4">Karyawan</th>
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4">Gaji Pokok</th>
                    <th class="px-6 py-4">Lembur</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @forelse($payrolls as $p)
                @php
                    $name = $p->employee->name ?? '-';
                    $paid = $p->status === 'Dibayar';
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $p->period_label }}</td>
                    <td class="px-6 py-4 text-[#a96f45]">
                        Rp {{ number_format($p->base_pay, 0, ',', '.') }}
                        @if($p->meal_pay > 0)
                            <p class="text-[10px] text-gray-400">+ uang makan Rp {{ number_format($p->meal_pay, 0, ',', '.') }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-[#a96f45]">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">Rp {{ number_format($p->total_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('owner.payrolls.status', $p->id) }}">
                            @csrf
                            @method('PUT')
                            <select name="status" data-current="{{ $p->status }}"
                                onchange="if (confirm('Ubah status gaji {{ addslashes($name) }}?')) { this.form.submit(); } else { this.value = this.dataset.current; }"
                                class="px-2.5 py-1 text-[10px] rounded-full border cursor-pointer focus:outline-none {{ $paid ? 'bg-green-50 text-green-600 border-green-100' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                <option value="Belum Dibayar" {{ !$paid ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="Dibayar" {{ $paid ? 'selected' : '' }}>Sudah Dibayar</option>
                            </select>
                        </form>
                        @if(abs($p->difference) >= 1)
                            <p class="mt-1 text-[10px] text-amber-600">
                                Presensi kini Rp {{ number_format($p->current_total, 0, ',', '.') }}
                                (selisih Rp {{ number_format(abs($p->difference), 0, ',', '.') }})
                            </p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data gaji. Data muncul otomatis dari presensi karyawan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL RIWAYAT GAJI KARYAWAN -->
    <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div @click.away="open = false; empId = ''" class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800 text-sm">Riwayat Gaji — <span x-text="names[empId]"></span></h3>
                <button type="button" @click="open = false; empId = ''" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="uppercase font-semibold text-[10px] text-[#c28455] bg-gray-50">
                    <tr>
                        <th class="px-3 py-3">Periode</th>
                        <th class="px-3 py-3">Gaji Pokok</th>
                        <th class="px-3 py-3">Lembur</th>
                        <th class="px-3 py-3">Total</th>
                        <th class="px-3 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    <template x-for="row in (histories[empId] || [])" :key="row.id">
                        <tr>
                            <td class="px-3 py-3 text-gray-500" x-text="row.period"></td>
                            <td class="px-3 py-3 text-[#a96f45]" x-text="row.base"></td>
                            <td class="px-3 py-3 text-[#a96f45]" x-text="row.overtime"></td>
                            <td class="px-3 py-3 font-bold text-gray-800" x-text="row.total"></td>
                            <td class="px-3 py-3">
                                <span class="px-2.5 py-1 text-[10px] rounded-full border"
                                      :class="row.paid ? 'bg-green-50 text-green-600 border-green-100' : 'bg-yellow-50 text-yellow-700 border-yellow-200'"
                                      x-text="row.status"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!(histories[empId] || []).length">
                        <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400">Belum ada riwayat gaji.</td></tr>
                    </template>
                </tbody>
            </table>
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