@extends('layouts.app')

@section('header_title', 'Manajemen Pesanan')

@section('content')
<div x-data="orderManager()" class="space-y-4">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ session('error') ?: $errors->first() }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('owner.orders.index') }}" class="{{ !$status ? 'bg-[#0f2137] text-white' : 'bg-white text-gray-600 border-gray-200' }} rounded-full border px-3 py-1.5 text-[10px] font-semibold">Semua</a>
            @foreach(['pending' => 'Menunggu Produksi', 'process' => 'Sedang Diproses', 'completed' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
                <a href="{{ route('owner.orders.index', ['status' => $key]) }}" class="{{ $status === $key ? 'bg-[#0f2137] text-white' : 'bg-white text-gray-600 border-gray-200' }} rounded-full border px-3 py-1.5 text-[10px] font-semibold">{{ $label }}</a>
            @endforeach
        </div>
        <div class="flex gap-2">
            <button type="button" @click="showWa = true" class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-2 text-[10px] font-semibold text-gray-600 hover:bg-gray-50">
                <span>▣</span> Dari WhatsApp
            </button>
            <button type="button" @click="openCreate()" class="inline-flex items-center gap-1.5 rounded-md bg-[#0f2137] px-3 py-2 text-[10px] font-semibold text-white hover:bg-[#183252]">
                <span>＋</span> Tambah Pesanan
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[980px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-3 py-3">No. Pesanan</th><th class="px-3 py-3">Nama Pelanggan</th><th class="px-3 py-3">No. HP</th><th class="px-3 py-3">Jumlah</th><th class="px-3 py-3">Varian Rasa</th><th class="px-3 py-3">Total Harga</th><th class="px-3 py-3">Catatan</th><th class="px-3 py-3">Tgl. Ambil</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
            @forelse($orders as $order)
                @php
                    $quantity = $order->details->sum('quantity');
                    $total = $order->details->sum(function ($detail) { return $detail->quantity * $detail->unit_price; });
                    $flavors = $order->details->map(function ($detail) { return optional($detail->productVariant)->name; })->filter()->implode(', ');
                    $statusStyles = ['pending' => ['Menunggu Produksi', 'bg-[#fff6d8] text-[#a17b1c]'], 'process' => ['Sedang Diproses', 'bg-[#e5f0ff] text-[#3975b5]'], 'completed' => ['Selesai', 'bg-[#dff5e9] text-[#348b66]'], 'cancelled' => ['Batal', 'bg-[#fee8e7] text-[#bd4d48]']];
                @endphp
                <tr class="hover:bg-[#fcfbf9]">
                    <td class="px-3 py-3 font-semibold text-[#0f2137]">P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-3 py-3">{{ $order->customer_name }}</td><td class="px-3 py-3">{{ $order->customer_phone }}</td><td class="px-3 py-3">{{ $quantity }} pcs</td><td class="px-3 py-3">{{ $flavors ?: '-' }}</td><td class="px-3 py-3 font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</td><td class="max-w-[130px] truncate px-3 py-3">{{ $order->notes ?: '—' }}</td><td class="px-3 py-3">{{ \Carbon\Carbon::parse($order->pickup_at)->format('d-m-Y') }}</td>
                    <td class="px-3 py-3"><span class="{{ $statusStyles[$order->status][1] ?? 'bg-gray-100 text-gray-500' }} rounded-full px-2 py-1 text-[9px] font-semibold">{{ $statusStyles[$order->status][0] ?? $order->status }}</span></td>
                    <td class="px-3 py-3"><div class="flex gap-1"><button @click="openEdit(@js($order->load('details.productVariant')))" class="rounded border border-gray-200 px-2 py-1 text-gray-500 hover:bg-gray-50">✎</button><button @click="openDelete('{{ route('owner.orders.destroy', $order) }}', 'P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}')" class="rounded border border-red-100 bg-red-50 px-2 py-1 text-red-500">♜</button><a href="{{ route('owner.orders.print_label', $order) }}" class="rounded border border-gray-200 px-2 py-1 text-gray-500" title="Download label">▧</a></div></td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-10 text-center text-xs text-gray-400">Belum ada data pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showForm" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="showForm=false" class="w-full max-w-xl rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4"><h3 class="text-sm font-bold text-[#0f2137]" x-text="editing ? 'Ubah Pesanan' : 'Tambah Pesanan Baru'"></h3><button @click="showForm=false" class="text-gray-400">✕</button></div>
            <form :action="formAction" method="POST" class="space-y-4 p-5">
                @csrf <template x-if="editing">@method('PUT')</template>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-[10px] font-semibold text-gray-600">NAMA PELANGGAN *<input name="customer_name" x-model="form.customer_name" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
                    <label class="text-[10px] font-semibold text-gray-600">NO. HP *<input name="customer_phone" x-model="form.customer_phone" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
                    <label class="text-[10px] font-semibold text-gray-600">JUMLAH (PCS) *<input name="variants[0][quantity]" type="number" min="1" x-model="form.quantity" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
                    <label class="text-[10px] font-semibold text-gray-600">VARIAN RASA
                        <select name="variants[0][id]" x-model="form.variant_id" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"><option value="">Pilih varian</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}</option>@endforeach</select>
                    </label>
                    <input type="hidden" name="variants[0][price]" :value="selectedPrice">
                    <label class="text-[10px] font-semibold text-gray-600">TANGGAL &amp; JAM AMBIL *<input name="pickup_at" type="datetime-local" x-model="form.pickup_at" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
                    <label class="text-[10px] font-semibold text-gray-600">STATUS<select name="status" x-model="form.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"><option value="pending">Menunggu Produksi</option><option value="process">Sedang Diproses</option><option value="completed">Selesai</option><option value="cancelled">Batal</option></select></label>
                </div>
                <label class="block text-[10px] font-semibold text-gray-600">NOTE / CATATAN<textarea name="notes" x-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="Catatan tambahan (opsional)"></textarea></label>
                <div class="flex gap-2 border-t border-gray-100 pt-4"><button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white" x-text="editing ? 'Simpan Perubahan' : 'Simpan Pesanan'"></button><button type="button" @click="showForm=false" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</button></div>
            </form>
        </div>
    </div>

    <div x-show="showWa" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl"><div class="flex justify-between"><h3 class="text-sm font-bold text-[#0f2137]">Parsing dari WhatsApp</h3><button @click="showWa=false" class="text-gray-400">✕</button></div><p class="mt-3 text-[10px] text-gray-500">Tempel teks chat sesuai format template untuk diisi otomatis.</p><form action="{{ route('owner.orders.parse_wa') }}" method="POST" class="mt-3">@csrf<textarea name="raw_template" required rows="8" class="w-full rounded-md border border-gray-200 p-3 text-xs" placeholder="Nama: Ibu Sari&#10;HP: 08123456789&#10;Waktu Ambil: 2026-07-01 14:00&#10;Catatan: Tolong dibungkus rapi"></textarea><div class="mt-3 flex gap-2"><button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white">Isi Otomatis &amp; Lanjut ke Form</button><button type="button" @click="showWa=false" class="rounded-md border border-gray-200 px-4 py-2 text-xs">Batal</button></div></form></div>
    </div>

    <div x-show="showDelete" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4"><div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl"><h3 class="text-sm font-bold text-[#0f2137]">Hapus Pesanan?</h3><p class="mt-3 text-xs text-gray-500">Yakin ingin menghapus <strong x-text="deleteName"></strong>? Tindakan ini tidak bisa dibatalkan.</p><form :action="deleteAction" method="POST" class="mt-5 flex gap-2">@csrf @method('DELETE')<button class="flex-1 rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white">Ya, Hapus</button><button type="button" @click="showDelete=false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs">Batal</button></form></div></div>
</div>

<script>
function orderManager() {
    const variants = @json($variants->keyBy('id'));
    const empty = { customer_name: '', customer_phone: '', quantity: 1, variant_id: '', pickup_at: '', status: 'pending', notes: '' };
    return {
        showForm: false, showWa: false, showDelete: false, editing: false, formAction: '{{ route('owner.orders.store') }}', deleteAction: '', deleteName: '', form: {...empty},
        get selectedPrice() { return (variants[this.form.variant_id] || {}).price || 0; },
        openCreate() { this.editing = false; this.form = {...empty}; this.formAction = '{{ route('owner.orders.store') }}'; this.showForm = true; },
        openEdit(order) { const d = order.details[0] || {}; this.editing = true; this.form = { customer_name: order.customer_name, customer_phone: order.customer_phone, quantity: d.quantity || 1, variant_id: d.product_variant_id || '', pickup_at: order.pickup_at.replace(' ', 'T').slice(0, 16), status: order.status, notes: order.notes || '' }; this.formAction = '{{ url('/owner/orders') }}/' + order.id; this.showForm = true; },
        openDelete(action, name) { this.deleteAction = action; this.deleteName = name; this.showDelete = true; }
    }
}
</script>
@endsection
