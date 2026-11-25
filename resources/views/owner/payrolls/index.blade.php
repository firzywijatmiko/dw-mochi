<!-- resources/views/owner/payrolls/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Penggajian')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="payrollFilter()" class="space-y-4">

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
            <form method="GET" action="{{ route('owner.payrolls.index') }}" class="flex-1 min-w-[140px]">
                @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">
                    Filter Bulan
                </label>

                <select name="month" onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 border border-gray-200 rounded-md text-xs bg-white text-gray-700 focus:border-[#0f2137] outline-none">
                    <option value="">Semua Bulan</option>
                    @foreach($monthOptions as $value => $label)
                        <option value="{{ $value }}" {{ $month === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Searchable Filter Karyawan -->
            <form method="GET" action="{{ route('owner.payrolls.index') }}" x-ref="empFilterForm" class="flex-1 min-w-[180px] relative" @click.away="empDropdownOpen = false">
                @if(request('month')) <input type="hidden" name="month" value="{{ request('month') }}"> @endif
                @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
                <input type="hidden" name="employee_id" :value="selectedEmpId">

                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">
                    Filter Karyawan
                </label>

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
                                    @click="selectAndSubmit('')"
                                    :class="selectedEmpId === '' ? 'bg-[#0f2137] text-white font-semibold' : 'text-gray-700 hover:bg-gray-50'"
                                    class="w-full text-left px-3 py-2 transition">
                                Semua Karyawan
                            </button>
                        </li>
                        <template x-for="emp in filteredEmployees" :key="emp.id">
                            <li>
                                <button type="button"
                                        @click="selectAndSubmit(String(emp.id))"
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
            </form>
        </div>
    </div>

    <!-- Tombol Reset Filter jika sedang aktif -->
    @if(request('month') || request('employee_id'))
        <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md border border-gray-200 text-xs">
            <span class="text-gray-600">Menampilkan data filter aktif.</span>
            <a href="{{ route('owner.payrolls.index') }}" class="text-[#0f2137] font-semibold hover:underline">
                Reset Filter ↺
            </a>
        </div>
    @endif

    <!-- Card Gabungan Tabel Penggajian & Pagination -->
    <div class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[780px] w-full text-left text-xs">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] uppercase text-[10px] font-semibold tracking-wider text-gray-500">
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
                <tbody class="divide-y divide-[#f1eeeb] text-gray-700">
                    @forelse($payrolls as $p)
                        @php
                            $name = $p->employee->name ?? '-';
                            $paid = $p->status === 'Dibayar';
                        @endphp
                        <tr class="hover:bg-[#fcfbf9] transition">
                            <td class="px-4 py-3 font-semibold text-[#0f2137]">{{ $name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $p->period_label }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                Rp {{ number_format($p->base_pay, 0, ',', '.') }}
                                @if($p->meal_pay > 0)
                                    <p class="text-[10px] text-gray-400">+ mkn Rp{{ number_format($p->meal_pay, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">Rp {{ number_format($p->sick_pay, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-bold text-gray-800 whitespace-nowrap">Rp {{ number_format($p->total_pay, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($paid)
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200 whitespace-nowrap">
                                        Sudah Dibayar
                                    </span>
                                    @if($p->paid_at)
                                        <p class="mt-1 text-[10px] text-gray-400">{{ $p->paid_at->format('d M Y H:i') }}</p>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('owner.payrolls.status', $p->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" data-current="{{ $p->status }}"
                                            onchange="if (confirm('Tandai gaji {{ addslashes($name) }} sudah dibayar? Setelah dibayar, status terkunci dan tidak bisa dikembalikan.')) { this.form.submit(); } else { this.value = this.dataset.current; }"
                                            class="px-2.5 py-1 text-[10px] font-medium rounded-md border cursor-pointer focus:outline-none bg-amber-50 text-amber-700 border-amber-200">
                                            <option value="Belum Dibayar" selected>Belum Dibayar</option>
                                            <option value="Dibayar">Sudah Dibayar</option>
                                        </select>
                                    </form>
                                @endif

                                @if(!$paid && abs($p->difference) >= 1)
                                    <p class="mt-1 text-[10px] text-amber-600">
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

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        @if(method_exists($payrolls, 'total'))
            @php $currentPerPage = $perPage ?? request('per_page', 10); @endphp
            <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('owner.payrolls.index') }}" method="GET" class="flex items-center gap-1.5">
                        @if(request('month')) <input type="hidden" name="month" value="{{ request('month') }}"> @endif
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
                        Menampilkan <strong class="text-gray-700">{{ $payrolls->firstItem() ?? 0 }} - {{ $payrolls->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $payrolls->total() }}</strong> data
                    </span>
                </div>

                @if($payrolls->hasPages())
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            @if($payrolls->onFirstPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $payrolls->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $current = $payrolls->currentPage();
                                $last    = $payrolls->lastPage();
                                $start   = max(1, $current - 2);
                                $end     = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $payrolls->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                                @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $payrolls->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                                <a href="{{ $payrolls->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                            @endif

                            @if($payrolls->hasMorePages())
                                <a href="{{ $payrolls->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('owner.payrolls.index') }}" method="GET" class="flex items-center gap-1.5">
                            @if(request('month')) <input type="hidden" name="month" value="{{ request('month') }}"> @endif
                            @if(request('employee_id')) <input type="hidden" name="employee_id" value="{{ request('employee_id') }}"> @endif
                            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                            <span class="text-gray-400">Ke hal:</span>
                            <input type="number" name="page" min="1" max="{{ $payrolls->lastPage() }}" placeholder="{{ $payrolls->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>

<script>
function payrollFilter() {
    const allEmployees = @json($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name])->values());
    const initialId = @json((string) ($selectedEmployee ?? ''));
    const initialEmp = allEmployees.find(e => String(e.id) === initialId);

    return {
        employees: allEmployees,
        empDropdownOpen: false,
        empSearch: '',
        selectedEmpId: initialId,
        selectedEmpName: initialEmp ? initialEmp.name : 'Semua Karyawan',

        get filteredEmployees() {
            if (!this.empSearch.trim()) return this.employees;
            const q = this.empSearch.toLowerCase().trim();
            return this.employees.filter(e => e.name.toLowerCase().includes(q));
        },

        selectAndSubmit(id) {
            this.selectedEmpId = id;
            this.empDropdownOpen = false;
            this.$nextTick(() => {
                this.$refs.empFilterForm.submit();
            });
        }
    }
}
</script>
@endsection