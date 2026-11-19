@extends('layouts.app')

@section('header_title', 'Manajemen Pengeluaran')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="expenseManager()" class="space-y-4">

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    @php $currentPerPage = $perPage ?? request('per_page', 10); @endphp

    {{-- Zona 1: Tombol Aksi Utama --}}
    <div class="flex flex-wrap items-center justify-end gap-2">
        <button type="button" @click="openModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#0f2137] px-3.5 py-2 text-xs font-medium text-white hover:bg-[#183252] transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Catat Pengeluaran
        </button>
    </div>

    {{-- Zona 2: Filter Toolbar Terpadu (Kategori + Tanggal + Live Search + Reset) --}}
    <div class="rounded-lg border border-[#e7e0d8] bg-white p-3 shadow-sm">
        <form action="{{ route('owner.expenses.index') }}" method="GET" id="expenseFilterForm" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            {{-- Kiri: Tab Filter Kategori --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('owner.expenses.index', request()->except('type', 'page')) }}" 
                   class="{{ !$type ? 'bg-[#0f2137] text-white border-[#0f2137]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-lg border px-3 py-1.5 text-xs font-medium transition">
                    Semua
                </a>
                <a href="{{ route('owner.expenses.index', array_merge(request()->except('page'), ['type' => 'material'])) }}" 
                   class="{{ $type === 'material' ? 'bg-[#0f2137] text-white border-[#0f2137]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-lg border px-3 py-1.5 text-xs font-medium transition">
                    Bahan Baku
                </a>
                <a href="{{ route('owner.expenses.index', array_merge(request()->except('page'), ['type' => 'payroll'])) }}" 
                   class="{{ $type === 'payroll' ? 'bg-[#0f2137] text-white border-[#0f2137]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-lg border px-3 py-1.5 text-xs font-medium transition">
                    Gaji Karyawan
                </a>
            </div>

            {{-- Kanan: Filter Tanggal, Live Search & Tombol Reset Permanen --}}
            <div class="flex flex-wrap items-center gap-2">
                @if($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <input type="hidden" name="per_page" value="{{ $currentPerPage }}">

                <div class="flex items-center gap-1.5">
                    <label for="start_date" class="text-xs text-gray-500 whitespace-nowrap">Dari:</label>
                    <input type="date" 
                           id="start_date"
                           name="start_date" 
                           value="{{ $startDate }}" 
                           onchange="document.getElementById('expenseFilterForm').submit()"
                           class="rounded-md border border-gray-200 px-2.5 py-1.5 text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                </div>

                <div class="flex items-center gap-1.5">
                    <label for="end_date" class="text-xs text-gray-500 whitespace-nowrap">Sampai:</label>
                    <input type="date" 
                           id="end_date"
                           name="end_date" 
                           value="{{ $endDate }}" 
                           onchange="document.getElementById('expenseFilterForm').submit()"
                           class="rounded-md border border-gray-200 px-2.5 py-1.5 text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                </div>

                {{-- Input Live Search --}}
                <div class="relative flex-1 sm:w-64">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           x-model.debounce.150ms="searchQuery"
                           @keydown.enter.prevent
                           placeholder="Cari item atau catatan..." 
                           class="w-full rounded-md border border-gray-200 pl-8 pr-7 py-1.5 text-xs text-gray-700 placeholder-gray-400 focus:border-[#0f2137] focus:outline-none">
                    
                    <button type="button" 
                            x-show="searchQuery.length > 0" 
                            x-cloak
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600"
                            title="Hapus pencarian">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tombol Reset (Selalu Tampil Sejak Awal) --}}
                <a href="{{ route('owner.expenses.index', ['per_page' => $currentPerPage]) }}" 
                   @click="if (!hasServerFilter) { $event.preventDefault(); searchQuery = ''; }"
                   :class="hasAnyFilter ? 'border-red-200 bg-red-50/70 text-red-600 hover:bg-red-100' : 'border-gray-200 bg-gray-50 text-gray-400 pointer-events-none opacity-60'"
                   class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1.5 text-xs font-medium transition">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Zona 3: Card Gabungan Tabel Catatan Pengeluaran & Pagination --}}
    <div class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[700px] w-full text-left text-xs">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Nama Item / Keperluan</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3 whitespace-nowrap">Nominal</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-700">
                    @forelse($expenses as $item)
                    @php
                        $categoryLabel = $item->type === 'payroll' ? 'Gaji Karyawan' : 'Bahan Baku';
                        $searchableText = strtolower($item->item_name . ' ' . ($item->notes ?? '') . ' ' . $categoryLabel);
                    @endphp
                    <tr x-show="matchesSearch(@js($searchableText))" class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->expense_date)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->type === 'payroll')
                                <span class="rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">Gaji Karyawan</span>
                            @else
                                <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">Bahan Baku</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-[#0f2137]">{{ $item->item_name }}</td>
                        <td class="px-4 py-3 max-w-[200px] truncate text-gray-500">{{ $item->notes ?: '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800 whitespace-nowrap">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-start gap-1.5 whitespace-nowrap">
                                @if($item->type === 'payroll')
                                    <span class="text-[11px] text-gray-400">Otomatis dari penggajian</span>
                                @else
                                    <button @click="openEditModal = true; editId = '{{ $item->id }}'; editItemName = @js($item->item_name); editAmount = formatRupiah('{{ $item->amount }}'); editDate = '{{ $item->expense_date }}'; editNotes = @js($item->notes ?? '');" 
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="h-3 w-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Ubah
                                    </button>
                                    <button @click="openDeleteModal = true; deleteId = '{{ $item->id }}'; deleteItemName = @js($item->item_name);" 
                                            class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1 text-[11px] font-medium text-red-600 hover:bg-red-50 transition">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-xs text-gray-400">
                            Belum ada catatan pengeluaran pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        @if(method_exists($expenses, 'total'))
            <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('owner.expenses.index') }}" method="GET" class="flex items-center gap-1.5">
                        @if($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
                        @if($startDate) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
                        @if($endDate) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif
                        <select name="per_page" onchange="this.form.submit()" class="rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 focus:border-[#0f2137] focus:outline-none">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="text-gray-400">per halaman</span>
                    </form>

                    <span class="hidden sm:inline text-gray-300">|</span>

                    <span class="text-gray-500">
                        Menampilkan <strong class="text-gray-700">{{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $expenses->total() }}</strong> data
                    </span>
                </div>

                @if($expenses->hasPages())
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            @if($expenses->onFirstPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $expenses->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $current = $expenses->currentPage();
                                $last    = $expenses->lastPage();
                                $start   = max(1, $current - 2);
                                $end     = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $expenses->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                                @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $expenses->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                                <a href="{{ $expenses->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                            @endif

                            @if($expenses->hasMorePages())
                                <a href="{{ $expenses->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('owner.expenses.index') }}" method="GET" class="flex items-center gap-1.5">
                            @if($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
                            @if($startDate) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
                            @if($endDate) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif
                            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                            <span class="text-gray-400">Ke hal:</span>
                            <input type="number" name="page" min="1" max="{{ $expenses->lastPage() }}" placeholder="{{ $expenses->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH PENGELUARAN -->
    <div x-cloak x-show="openModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative" @click.away="openModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-[#0f2137]">Catat Pengeluaran Baru</h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('owner.expenses.store') }}" method="POST" class="space-y-3">
                @csrf
                <label class="block text-[11px] font-semibold text-gray-600">Nama Item / Keperluan *
                    <input type="text" name="item_name" required placeholder="cth: Bahan baku tepung ketan" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Nominal (Rp) *
                    <input type="text" name="amount" x-data="{ val: '' }" x-model="val" @input="val = formatRupiah(val)" required placeholder="0" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Tanggal *
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Keterangan
                    <input type="text" name="notes" placeholder="Opsional" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-medium hover:bg-[#183252] transition">Simpan</button>
                    <button type="button" @click="openModal = false" class="flex-1 border border-gray-200 px-4 py-2 rounded-md text-xs text-gray-600 hover:bg-gray-50 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PENGELUARAN -->
    <div x-cloak x-show="openEditModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative" @click.away="openEditModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-[#0f2137]">Ubah Catatan Pengeluaran</h3>
                <button @click="openEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="'{{ url('/owner/pengeluaran') }}/' + editId" method="POST" class="space-y-3">
                @csrf @method('PUT')
                <label class="block text-[11px] font-semibold text-gray-600">Nama Item / Keperluan *
                    <input type="text" name="item_name" x-model="editItemName" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Nominal (Rp) *
                    <input type="text" name="amount" x-model="editAmount" @input="editAmount = formatRupiah(editAmount)" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Tanggal *
                    <input type="date" name="expense_date" x-model="editDate" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[11px] font-semibold text-gray-600">Keterangan
                    <input type="text" name="notes" x-model="editNotes" placeholder="Opsional" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-medium hover:bg-[#183252] transition">Simpan Perubahan</button>
                    <button type="button" @click="openEditModal = false" class="flex-1 border border-gray-200 px-4 py-2 rounded-md text-xs text-gray-600 hover:bg-gray-50 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-cloak x-show="openDeleteModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative" @click.away="openDeleteModal = false">
            <h3 class="text-sm font-bold text-[#0f2137]">Hapus Catatan Pengeluaran?</h3>
            <p class="mt-2 text-xs text-gray-500">Yakin ingin menghapus catatan <strong x-text="deleteItemName"></strong>?</p>
            <div class="flex gap-2 mt-5">
                <form :action="'{{ url('/owner/pengeluaran') }}/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-md text-xs font-medium transition">Ya, Hapus</button>
                </form>
                <button type="button" @click="openDeleteModal = false" class="flex-1 border border-gray-200 py-2 rounded-md text-xs text-gray-600 hover:bg-gray-50 transition">Batal</button>
            </div>
        </div>
    </div>

</div>

<script>
function expenseManager() {
    return {
        openModal: false,
        openEditModal: false,
        openDeleteModal: false,
        deleteId: '',
        deleteItemName: '',
        editId: '',
        editItemName: '',
        editAmount: '',
        editDate: '',
        editNotes: '',
        searchQuery: @js($search ?? ''),
        hasServerFilter: @json((bool) ($type || request()->filled('start_date') || request()->filled('end_date'))),

        get hasAnyFilter() {
            return this.hasServerFilter || (this.searchQuery && this.searchQuery.trim().length > 0);
        },

        matchesSearch(text) {
            if (!this.searchQuery || !this.searchQuery.trim()) return true;
            return text.includes(this.searchQuery.toLowerCase().trim());
        },

        formatRupiah(value) {
            if (!value) return '';
            let numberString = value.toString().replace(/[^,\d]/g, '').toString();
            if (numberString.length > 1) {
                numberString = numberString.replace(/^0+/, '');
            }
            let split = numberString.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        }
    }
}
</script>
@endsection