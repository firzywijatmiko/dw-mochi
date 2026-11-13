
@extends('layouts.app')

@section('header_title', 'Penggajian')

@section('content')
<div class="space-y-4">

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Ringkasan + Filter (Bulan & Karyawan) -->
    <div class="flex flex-col sm:flex-row flex-wrap sm:items-end gap-3">
        <div class="flex-1 bg-white rounded-lg shadow-sm border border-[#e7e0d8] px-4 py-3 flex items-center gap-3">
            <div class="p-2 bg-[#fff6d8] text-[#a17b1c] rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[9px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">
                    Total Belum Dibayar
                </p>
                <p class="text-base font-bold text-gray-800">
                    Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap flex-1 gap-3">

            <!-- Filter Bulan -->
            <form method="GET" action="{{ route('owner.payrolls.index') }}" class="flex-1 min-w-[120px]">
                @if(request('employee_id'))
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                @endif

                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">
                    Filter Bulan
                </label>

                <select name="month" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none">
                    <option value="">Semua Bulan</option>
                    @foreach($monthOptions as $value => $label)
                        <option value="{{ $value }}" {{ $month === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Filter Karyawan -->
            <form method="GET" action="{{ route('owner.payrolls.index') }}" class="flex-1 min-w-[150px]">
                @if(request('month'))
                    <input type="hidden" name="month" value="{{ request('month') }}">
                @endif

                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">
                    Filter Karyawan
                </label>

                <select name="employee_id" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none">
                    <option value="">— Semua Karyawan —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ (isset($selectedEmployee) && $selectedEmployee == $emp->id) ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </form>

        </div>
    </div>

    <!-- Tombol Reset Filter jika sedang aktif -->
    @if(request('month') || request('employee_id'))
        <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md border border-gray-200 text-xs">
            <span class="text-gray-600">Menampilkan data filter aktif.</span>
            <a href="{{ route('owner.payrolls.index') }}"
                class="text-[#0f2137] font-semibold hover:underline">
                Reset Filter ↺
            </a>
        </div>
    @endif

    <!-- Tabel Penggajian -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[780px] w-full text-left text-[10px]">

            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Gaji Pokok</th>
                    <th class="px-4 py-3">Lembur</th>
                    <th class="px-4 py-3">Uang Sakit</th>
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

                        <td class="px-4 py-3 font-semibold text-[#0f2137]">
                            {{ $name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $p->period_label }}
                        </td>

                        <td class="px-4 py-3">
                            Rp {{ number_format($p->base_pay, 0, ',', '.') }}

                            @if($p->meal_pay > 0)
                                <p class="text-[9px] text-gray-400">
                                    + mkn Rp{{ number_format($p->meal_pay, 0, ',', '.') }}
                                </p>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3">
                            Rp {{ number_format($p->sick_pay, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 font-bold text-gray-800">
                            Rp {{ number_format($p->total_pay, 0, ',', '.') }}
                        </td>

                        <!-- Status Pembayaran -->
                        <td class="px-4 py-3">

                            @if($paid)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-[9px] font-semibold rounded-full border bg-[#dff5e9] text-[#348b66] border-[#348b66]">
                                    🔒 Sudah Dibayar
                                </span>

                                @if($p->paid_at)
                                    <p class="mt-1 text-[9px] text-gray-400">
                                        {{ $p->paid_at->format('d M Y H:i') }}
                                    </p>
                                @endif

                            @else
                                <form method="POST" action="{{ route('owner.payrolls.status', $p->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <select name="status" data-current="{{ $p->status }}"
                                        onchange="if (confirm('Tandai gaji {{ addslashes($name) }} sudah dibayar? Setelah dibayar, status terkunci dan tidak bisa dikembalikan.')) { this.form.submit(); } else { this.value = this.dataset.current; }"
                                        class="px-2 py-1 text-[9px] font-semibold rounded-full border cursor-pointer focus:outline-none bg-[#fff6d8] text-[#a17b1c] border-[#a17b1c]">

                                        <option value="Belum Dibayar" selected>
                                            Belum Dibayar
                                        </option>

                                        <option value="Dibayar">
                                            Sudah Dibayar
                                        </option>

                                    </select>
                                </form>
                            @endif

                            <!-- Informasi Selisih Presensi -->
                            @if(!$paid && abs($p->difference) >= 1)
                                <p class="mt-1 text-[9px] text-amber-600">
                                    Presensi kini Rp{{ number_format($p->current_total, 0, ',', '.') }}
                                    (selisih Rp{{ number_format(abs($p->difference), 0, ',', '.') }})
                                </p>
                            @endif

                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-xs text-gray-400">
                            Tidak ada data gaji yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <div class="mt-4">
        {{ $payrolls->links() }}
    </div>

</div>
@endsection
