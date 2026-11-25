@extends('layouts.app')

@section('header_title', 'Manajemen Pesanan')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="orderManager()" class="space-y-4">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ session('error') ?: $errors->first() }}</div>
    @endif

    {{-- Zona 1: Tombol Aksi Utama --}}
    <div class="flex flex-wrap items-center justify-end gap-2">
        <form action="{{ route('owner.orders.print_bulk') }}" method="POST" target="_blank" id="bulkPrintForm" @submit="handleBulkPrint()">
            @csrf
            <template x-for="id in selectedOrders" :key="id">
                <input type="hidden" name="order_ids[]" :value="id">
            </template>
            <button type="submit" 
                    :disabled="selectedOrders.length === 0"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Terpilih (<span x-text="selectedOrders.length"></span>)
            </button>
        </form>

        <button type="button" @click="showWa = true" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            Dari WhatsApp
        </button>

        <button type="button" @click="openCreate()" class="inline-flex items-center gap-1.5 rounded-lg bg-[#0f2137] px-3 py-2 text-xs font-medium text-white hover:bg-[#183252] transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Pesanan
        </button>
    </div>

    {{-- Zona 2: Filter Toolbar Terpadu (Status + Tanggal Ambil + Live Search + Reset) --}}
    <div class="rounded-lg border border-[#e7e0d8] bg-white p-3 shadow-sm">
        <form action="{{ route('owner.orders.index') }}" method="GET" id="filterForm" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            {{-- Kiri: Tab Filter Status --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('owner.orders.index', request()->except('status', 'page')) }}" 
                   class="{{ !$status ? 'bg-[#0f2137] text-white border-[#0f2137]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-lg border px-3 py-1.5 text-xs font-medium transition">
                    Semua
                </a>
                @foreach(['pending' => 'Menunggu Produksi', 'process' => 'Sedang Diproses', 'completed' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
                    <a href="{{ route('owner.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}" 
                       class="{{ $status === $key ? 'bg-[#0f2137] text-white border-[#0f2137]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }} rounded-lg border px-3 py-1.5 text-xs font-medium transition">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Kanan: Filter Tanggal Ambil, Live Search & Tombol Reset Permanen --}}
            <div class="flex flex-wrap items-center gap-2">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                {{-- Filter Tanggal Ambil --}}
                <div class="flex items-center gap-1.5">
                    <label for="pickup_date" class="text-xs text-gray-500 whitespace-nowrap">Tgl. Ambil:</label>
                    <input type="date" 
                           id="pickup_date"
                           name="pickup_date" 
                           value="{{ $pickupDate ?? '' }}" 
                           onchange="document.getElementById('filterForm').submit()"
                           class="rounded-md border border-gray-200 px-2.5 py-1.5 text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                </div>

                {{-- Input Live Search --}}
                <div class="relative flex-1 sm:w-64">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="search"
                           x-model.debounce.150ms="searchQuery"
                           placeholder="Cari nama, no. HP, atau ID..." 
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

                {{-- Tombol Reset --}}
                <a href="{{ route('owner.orders.index', ['per_page' => $perPage]) }}" 
                   @click="if (!hasServerFilter) { $event.preventDefault(); searchQuery = ''; }"
                   :class="hasAnyFilter ? 'border-red-200 bg-red-50/70 text-red-600 hover:bg-red-100' : 'border-gray-200 bg-gray-50 text-gray-400 pointer-events-none opacity-60'"
                   class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1.5 text-xs font-medium transition">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Zona 3: Card Gabungan Tabel Pesanan & Pagination --}}
    <div class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[1040px] w-full text-left text-xs">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-3 py-3 w-8 text-center">
                            <input type="checkbox" @click="toggleSelectAll($event)" class="rounded border-gray-300" title="Pilih semua pesanan selesai">
                        </th>
                        <th class="px-3 py-3 whitespace-nowrap">No. Pesanan</th>
                        <th class="px-3 py-3">Pelanggan</th>
                        <th class="px-3 py-3">No. HP</th>
                        <th class="px-3 py-3">Jumlah</th>
                        <th class="px-3 py-3">Varian Rasa</th>
                        <th class="px-3 py-3 whitespace-nowrap">Total Harga</th>
                        <th class="px-3 py-3">Catatan</th>
                        <th class="px-3 py-3 whitespace-nowrap">Tgl. Ambil</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-700">
                @forelse($orders as $order)
                    @php
                        $orderCode = 'P-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
                        $quantity = $order->details->sum('quantity');
                        $total = $order->details->sum(function ($detail) { return $detail->quantity * $detail->unit_price; });
                        $flavors = $order->details->map(function ($detail) { return optional($detail->productVariant)->name; })->filter()->implode(', ');
                        $statusStyles = [
                            'pending'   => ['Menunggu Produksi', 'bg-amber-50 text-amber-700 border-amber-200'], 
                            'process'   => ['Sedang Diproses', 'bg-blue-50 text-blue-700 border-blue-200'], 
                            'completed' => ['Selesai', 'bg-emerald-50 text-emerald-700 border-emerald-200'], 
                            'cancelled' => ['Batal', 'bg-red-50 text-red-700 border-red-200']
                        ];
                        $isCompleted = ($order->status === 'completed');
                        $searchableText = strtolower($orderCode . ' ' . $order->customer_name . ' ' . $order->customer_phone . ' ' . $flavors);
                    @endphp
                    <tr x-show="matchesSearch(@js($searchableText))" class="hover:bg-[#fcfbf9] transition">
                        <td class="px-3 py-3 text-center">
                            @if($isCompleted)
                                <input type="checkbox" value="{{ $order->id }}" x-model.number="selectedOrders" class="rounded border-gray-300">
                            @else
                                <input type="checkbox" disabled class="rounded border-gray-200 bg-gray-100 cursor-not-allowed opacity-40" title="Hanya untuk pesanan selesai">
                            @endif
                        </td>
                        <td class="px-3 py-3 font-semibold text-[#0f2137] whitespace-nowrap">{{ $orderCode }}</td>
                        <td class="px-3 py-3 font-medium">{{ $order->customer_name }}</td>
                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $order->customer_phone }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $quantity }} pcs</td>
                        <td class="px-3 py-3">{{ $flavors ?: '-' }}</td>
                        <td class="px-3 py-3 font-semibold whitespace-nowrap">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        <td class="max-w-[130px] truncate px-3 py-3 text-gray-500">{{ $order->notes ?: '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($order->pickup_at)->format('d-m-Y H:i') }}</td>
                        <td class="px-3 py-3">
                            <div class="flex flex-col items-start gap-1">
                                <span class="{{ $statusStyles[$order->status][1] ?? 'bg-gray-100 text-gray-600 border-gray-200' }} rounded-md border px-2 py-0.5 text-[10px] font-medium whitespace-nowrap">
                                    {{ $statusStyles[$order->status][0] ?? $order->status }}
                                </span>
                                
                                @if($isCompleted)
                                    @if($order->is_label_printed)
                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-medium bg-teal-50 text-teal-700 border border-teal-200 whitespace-nowrap">
                                            ✓ Sudah dicetak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-medium bg-orange-50 text-orange-700 border border-orange-200 whitespace-nowrap">
                                            • Belum dicetak
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center justify-start gap-1.5 whitespace-nowrap">
                                <button @click="openEdit(@js($order->load('details.productVariant')))" 
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="h-3 w-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Ubah
                                </button>

                                <button @click="openDelete('{{ route('owner.orders.destroy', $order) }}', '{{ $orderCode }}')" 
                                        class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1 text-[11px] font-medium text-red-600 hover:bg-red-50 transition">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                                
                                {{-- Tombol Cetak Label (Belum dicetak = Navy Gelap) vs Cetak Lagi (Sudah dicetak = Abu-abu Lembut) --}}
                                @if($isCompleted)
                                    @if($order->is_label_printed)
                                        <button type="button" 
                                                @click="openLabel(@js($order->load('details.productVariant')), '{{ route('owner.orders.print_label', $order) }}')" 
                                                class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-200 transition">
                                            <svg class="h-3 w-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Cetak Lagi
                                        </button>
                                    @else
                                        <button type="button" 
                                                @click="openLabel(@js($order->load('details.productVariant')), '{{ route('owner.orders.print_label', $order) }}')" 
                                                class="inline-flex items-center gap-1 rounded-md bg-[#0f2137] px-2.5 py-1 text-[11px] font-medium text-white hover:bg-[#183252] transition">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak Label
                                        </button>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md border border-gray-100 bg-gray-50 px-2.5 py-1 text-[11px] text-gray-300 cursor-not-allowed" title="Label hanya untuk pesanan selesai">
                                        Cetak Label
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-10 text-center text-xs text-gray-400">
                            {{ ($status || $pickupDate || $search) ? 'Tidak ada pesanan yang sesuai dengan filter.' : 'Belum ada data pesanan.' }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('owner.orders.index') }}" method="GET" class="flex items-center gap-1.5">
                    @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                    @if($pickupDate) <input type="hidden" name="pickup_date" value="{{ $pickupDate }}"> @endif
                    @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                    <select name="per_page" onchange="this.form.submit()" class="rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        @foreach([5, 10, 25, 50] as $size)
                            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="text-gray-400">per halaman</span>
                </form>

                <span class="hidden sm:inline text-gray-300">|</span>

                <span class="text-gray-500">
                    Menampilkan <strong class="text-gray-700">{{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $orders->total() }}</strong> data
                </span>
            </div>

            @if($orders->hasPages())
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1">
                        @if($orders->onFirstPage())
                            <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @endif

                        @php
                            $current = $orders->currentPage();
                            $last    = $orders->lastPage();
                            $start   = max(1, $current - 2);
                            $end     = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $orders->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                            @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $current)
                                <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                            @else
                                <a href="{{ $orders->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                            <a href="{{ $orders->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                        @endif

                        @if($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif
                    </div>

                    <form action="{{ route('owner.orders.index') }}" method="GET" class="flex items-center gap-1.5">
                        @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                        @if($pickupDate) <input type="hidden" name="pickup_date" value="{{ $pickupDate }}"> @endif
                        @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <span class="text-gray-400">Ke hal:</span>
                        <input type="number" name="page" min="1" max="{{ $orders->lastPage() }}" placeholder="{{ $orders->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL FORM PESANAN -->
    <div x-show="showForm" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="showForm=false" class="w-full max-w-xl rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-bold text-[#0f2137]" x-text="editing ? 'Ubah Pesanan' : 'Tambah Pesanan Baru'"></h3>
                <button @click="showForm=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form :action="formAction" method="POST" class="space-y-4 p-5 max-h-[80vh] overflow-y-auto">
                @csrf 
                <template x-if="editing">@method('PUT')</template>
                
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-[11px] font-semibold text-gray-600">Nama Pelanggan *
                        <input name="customer_name" x-model="form.customer_name" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    <label class="text-[11px] font-semibold text-gray-600">No. HP *
                        <input name="customer_phone" x-model="form.customer_phone" required 
                               type="text" maxlength="13" pattern="^08[0-9]{8,11}$" 
                               @input="form.customer_phone = form.customer_phone.replace(/[^0-9]/g, '')"
                               class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    
                    <label class="text-[11px] font-semibold text-gray-600">Tanggal &amp; Jam Ambil *
                        <input name="pickup_at" type="datetime-local" x-model="form.pickup_at" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    <label class="text-[11px] font-semibold text-gray-600">Status
                        <select name="status" x-model="form.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                            <option value="pending">Menunggu Produksi</option>
                            <option value="process">Sedang Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Batal</option>
                        </select>
                    </label>

                    <div class="col-span-1 sm:col-span-2 rounded-lg border border-gray-100 p-3 bg-gray-50">
                        <label class="mb-2 block text-[11px] font-semibold text-gray-600">Produk yang Dipesan *</label>
                        <template x-for="(item, index) in form.variants" :key="index">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="flex-1">
                                    <select :name="'variants[' + index + '][id]'" x-model="item.variant_id" @change="updatePrice(index)" required class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-xs">
                                        <option value="">Pilih varian</option>
                                        @foreach($variants as $variant)
                                            <option value="{{ $variant->id }}" x-bind:disabled="isVariantDisabled({{ $variant->id }}, index)">
                                                {{ $variant->name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input :name="'variants[' + index + '][quantity]'" type="number" min="1" x-model="item.quantity" required class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-xs" placeholder="Qty">
                                </div>
                                <input type="hidden" :name="'variants[' + index + '][price]'" :value="item.price">
                                <div>
                                    <button type="button" @click="removeVariant(index)" x-show="form.variants.length > 1" class="rounded-md border border-red-200 bg-red-50 p-2 text-red-500 hover:bg-red-100">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="addVariant()" x-show="canAddMoreVariants()" class="mt-1 inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-50">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Tambah Varian
                        </button>
                    </div>
                </div>
                
                <label class="block text-[11px] font-semibold text-gray-600">Catatan
                    <textarea name="notes" x-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></textarea>
                </label>
                
                <div class="flex gap-2 border-t border-gray-100 pt-4">
                    <button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-medium text-white hover:bg-[#183252]" x-text="editing ? 'Simpan Perubahan' : 'Simpan Pesanan'"></button>
                    <button type="button" @click="showForm=false" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL WHATSAPP PARSER -->
    <div x-show="showWa" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl" @click.away="showWa=false">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-[#0f2137]">Parsing dari WhatsApp</h3>
                <button @click="showWa=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form action="{{ route('owner.orders.parse_wa') }}" method="POST" class="mt-3" @submit.prevent="submitWaForm($event)">
                @csrf
                <textarea name="raw_template" x-model="waText" required rows="8" class="w-full rounded-md border border-gray-200 p-3 text-xs" placeholder="Tempel teks WhatsApp di sini..."></textarea>
                
                <p x-show="waError" x-text="waError" class="mt-2 text-xs font-semibold text-red-500" style="display:none;"></p>
                
                <div class="mt-3 flex gap-2">
                    <button type="submit" :disabled="isParsingWa" class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-medium text-white disabled:opacity-50">
                        <span x-text="isParsingWa ? 'Mengecek...' : 'Isi Otomatis & Lanjut'"></span>
                    </button>
                    <button type="button" @click="showWa=false; waError=''; waText=''" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div x-show="showDelete" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
            <h3 class="text-sm font-bold text-[#0f2137]">Hapus Pesanan?</h3>
            <p class="mt-2 text-xs text-gray-500">Yakin ingin menghapus pesanan <strong x-text="deleteName"></strong>?</p>
            <form :action="deleteAction" method="POST" class="mt-5 flex gap-2">
                @csrf @method('DELETE')
                <button class="flex-1 rounded-md bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Ya, Hapus</button>
                <button type="button" @click="showDelete=false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50">Batal</button>
            </form>
        </div>
    </div>

    <!-- MODAL LABEL SATUAN -->
    <div x-cloak x-show="showLabel" @keydown.escape.window="showLabel=false" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="showLabel=false" class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl">
            <div class="flex items-center justify-between px-2 pb-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-[#0f2137]">Preview Label Pesanan</h3>
                <button type="button" @click="showLabel=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="mx-auto my-4 w-full rounded-lg border-2 border-black bg-white p-4 text-black shadow-sm" style="max-width: 360px;">
                <div class="flex items-center justify-between border-b border-black pb-2 mb-2 text-xs font-bold uppercase tracking-wider">
                    <span>DW Mochi</span>
                    <span x-text="'P-' + String(labelOrder.id).padStart(3, '0')"></span>
                </div>
                <div class="text-center my-3">
                    <div class="text-[9px] uppercase tracking-widest text-gray-600">Penerima Pesanan</div>
                    <div class="text-base font-bold uppercase tracking-wide" x-text="labelOrder.customer_name"></div>
                    <div class="text-[10px] text-gray-700" x-text="labelOrder.customer_phone"></div>
                </div>
                <div class="border-t border-black my-2"></div>
                <div class="text-[10px] font-bold uppercase mb-1">Detail Pesanan:</div>
                <template x-for="detail in labelOrder.details" :key="detail.id">
                    <div class="flex justify-between text-[11px] mb-1">
                        <span class="font-medium" x-text="'• ' + (detail.product_variant ? detail.product_variant.name : '')"></span>
                        <span class="font-bold" x-text="detail.quantity + ' pcs'"></span>
                    </div>
                </template>
                <div class="mt-3 grid grid-cols-5 border-t border-black pt-2 text-center text-[10px]">
                    <div class="col-span-3 border-r border-black pr-1">
                        <span class="block text-[8px] text-gray-600 uppercase">Waktu Ambil</span>
                        <strong class="font-bold text-[10px]" x-text="formatPickup(labelOrder.pickup_at)"></strong>
                    </div>
                    <div class="col-span-2 pl-1">
                        <span class="block text-[8px] text-gray-600 uppercase">Status</span>
                        <strong class="font-bold uppercase text-[10px]" x-text="labelOrder.status"></strong>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-2 px-2">
                <button type="button" @click="printLabel()" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-md bg-[#0f2137] px-4 py-2 text-xs font-medium text-white hover:bg-[#183252]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak PDF
                </button>
                <button type="button" @click="showLabel=false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function orderManager() {
    const variants = @json($variants->keyBy('id'));
    const totalVariantChoices = Object.keys(variants).length;

    const empty = { 
        customer_name: '', customer_phone: '', pickup_at: '', 
        status: 'pending', notes: '', 
        variants: [{ variant_id: '', quantity: 1, price: 0 }] 
    };
    
    const printableOrderIds = @json($orders->where('status', 'completed')->pluck('id')->values());

    return {
        showForm: false, showWa: false, showDelete: false, showLabel: false, editing: false, 
        formAction: '{{ route('owner.orders.store') }}', deleteAction: '', deleteName: '', 
        labelOrder: { details: [] }, labelUrl: '', 
        selectedOrders: [], 
        searchQuery: @js($search ?? ''),
        hasServerFilter: @json((bool) ($status || $pickupDate || $search)),
        form: JSON.parse(JSON.stringify(empty)),

        waText: '',
        waError: '',
        isParsingWa: false,

        get hasAnyFilter() {
            return this.hasServerFilter || (this.searchQuery && this.searchQuery.trim().length > 0);
        },

        matchesSearch(text) {
            if (!this.searchQuery || !this.searchQuery.trim()) return true;
            return text.includes(this.searchQuery.toLowerCase().trim());
        },

        async submitWaForm(event) {
            this.waError = '';
            this.isParsingWa = true;

            try {
                const response = await fetch('{{ route('owner.orders.parse_wa') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ raw_template: this.waText })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    this.waError = result.message || 'Format Pesan Tidak Sesuai';
                } else {
                    event.target.submit(); 
                }
            } catch (error) {
                this.waError = 'Terjadi kesalahan pada server. Coba lagi.';
            } finally {
                this.isParsingWa = false;
            }
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedOrders = [...printableOrderIds];
            } else {
                this.selectedOrders = [];
            }
        },

        handleBulkPrint() {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        },

        addVariant() { this.form.variants.push({ variant_id: '', quantity: 1, price: 0 }); },
        removeVariant(index) { if (this.form.variants.length > 1) this.form.variants.splice(index, 1); },
        updatePrice(index) {
            let vid = this.form.variants[index].variant_id;
            this.form.variants[index].price = (variants[vid] || {}).price || 0;
        },
        isVariantDisabled(variantId, currentIndex) {
            return this.form.variants.some((v, idx) => idx !== currentIndex && v.variant_id == variantId);
        },
        canAddMoreVariants() { return this.form.variants.length < totalVariantChoices; },

        openCreate() { 
            this.editing = false; 
            this.form = JSON.parse(JSON.stringify(empty)); 
            this.formAction = '{{ route('owner.orders.store') }}'; 
            this.showForm = true; 
        },
        openEdit(order) { 
            this.editing = true; 
            let loadedVariants = order.details.map(d => ({ variant_id: d.product_variant_id, quantity: d.quantity, price: d.unit_price }));
            this.form = { 
                customer_name: order.customer_name, customer_phone: order.customer_phone, 
                pickup_at: order.pickup_at.replace(' ', 'T').slice(0, 16), status: order.status, 
                notes: order.notes || '', variants: loadedVariants.length ? loadedVariants : [{ variant_id: '', quantity: 1, price: 0 }]
            }; 
            this.formAction = '{{ url('/owner/orders') }}/' + order.id; 
            this.showForm = true; 
        },
        openDelete(action, name) { this.deleteAction = action; this.deleteName = name; this.showDelete = true; },
        openLabel(order, url) { this.labelOrder = order; this.labelUrl = url; this.showLabel = true; },
        printLabel() { 
            window.open(this.labelUrl, 'labelPrintPopup', 'width=520,height=720,resizable=yes,scrollbars=yes');
            setTimeout(() => { window.location.reload(); }, 1200);
        },
        formatPickup(value) { 
            if (!value) return '-'; 
            return new Date(value.replace(' ', 'T')).toLocaleString('id-ID', { 
                day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' 
            }); 
        }
    }
}
</script>
@endsection