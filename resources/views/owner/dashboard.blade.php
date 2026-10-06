@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-[#0f2137]">Ringkasan Operasional</h1>
            <p class="mt-1 text-xs text-gray-500">Pantau pesanan dan aktivitas DW Mochi hari ini.</p>
        </div>
        <span class="hidden rounded-full bg-white px-3 py-1.5 text-xs text-gray-500 shadow-sm sm:inline-flex">
            {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-[#e7e0d8] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold text-gray-500">Pesanan Aktif Hari Ini</p>
                <span class="rounded-md bg-[#fff4e8] p-1.5 text-[#c28455]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xl font-bold text-[#0f2137]">{{ $statistics['active_orders'] }}</p>
            <p class="mt-1 text-[10px] text-gray-400">pesanan sedang berjalan</p>
        </div>

        <div class="rounded-lg border border-[#e7e0d8] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold text-gray-500">Pesanan Selesai</p>
                <span class="rounded-md bg-[#edf8f2] p-1.5 text-[#3f9b72]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.8" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xl font-bold text-[#0f2137]">{{ $statistics['completed_today'] }}</p>
            <p class="mt-1 text-[10px] text-gray-400">sudah diambil hari ini</p>
        </div>

        <div class="rounded-lg border border-[#e7e0d8] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold text-gray-500">Pemasukan Minggu Ini</p>
                <span class="rounded-md bg-[#eef5fc] p-1.5 text-[#4c83b5]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.8" d="M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2m0-8V7m0 1v8m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xl font-bold text-[#0f2137]">Rp {{ number_format($statistics['weekly_revenue'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[10px] text-gray-400">dari pesanan selesai</p>
        </div>

        <div class="rounded-lg border border-[#e7e0d8] bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold text-gray-500">Total Pesanan Minggu</p>
                <span class="rounded-md bg-[#f5effb] p-1.5 text-[#9366b5]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.8" d="M7 4h10v16H7zM9 8h6M9 12h6M9 16h4"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xl font-bold text-[#0f2137]">{{ $statistics['weekly_orders'] }}</p>
            <p class="mt-1 text-[10px] text-gray-400">termasuk yang aktif</p>
        </div>
    </div>

    <a href="{{ route('owner.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-[#0f2137] px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#183252]">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
        Tambah Pesanan Baru
    </a>

    <section class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#eee9e4] px-4 py-3">
            <h2 class="text-xs font-bold text-[#0f2137]">Pesanan Hari Ini &amp; Mendatang</h2>
            <a href="{{ route('owner.orders.index') }}" class="text-[10px] font-semibold text-[#c28455] hover:underline">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-[#faf9f7] text-[9px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">No. Pesanan</th>
                        <th class="px-4 py-2.5 font-semibold">Nama Pelanggan</th>
                        <th class="px-4 py-2.5 font-semibold">Jumlah (Pcs)</th>
                        <th class="px-4 py-2.5 font-semibold">Tgl. Ambil</th>
                        <th class="px-4 py-2.5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-[10px] text-gray-600">
                    @forelse ($recentOrders as $order)
                        @php
                            $quantity = $order->details->sum('quantity');
                            $status = [
                                'pending' => ['Menunggu Produksi', 'bg-[#fff6d8] text-[#a17b1c]'],
                                'process' => ['Sedang Diproses', 'bg-[#e5f0ff] text-[#3975b5]'],
                            ][$order->status] ?? ['Tidak Diketahui', 'bg-gray-100 text-gray-500'];
                        @endphp
                        <tr class="hover:bg-[#fcfbf9]">
                            <td class="px-4 py-3 font-semibold text-[#0f2137]">P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3">{{ $quantity }} pcs</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($order->pickup_at)->format('d M Y') }}</td>
                            <td class="px-4 py-3"><span class="{{ $status[1] }} inline-flex rounded-full px-2 py-1 text-[9px] font-semibold">{{ $status[0] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada pesanan hari ini atau mendatang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
