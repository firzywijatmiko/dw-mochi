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

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('owner.orders.index') }}" class="{{ !$status ? 'bg-[#0f2137] text-white' : 'bg-white text-gray-600 border-gray-200' }} rounded-full border px-3 py-1.5 text-[10px] font-semibold">Semua</a>
            @foreach(['pending' => 'Menunggu Produksi', 'process' => 'Sedang Diproses', 'completed' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
                <a href="{{ route('owner.orders.index', ['status' => $key]) }}" class="{{ $status === $key ? 'bg-[#0f2137] text-white' : 'bg-white text-gray-600 border-gray-200' }} rounded-full border px-3 py-1.5 text-[10px] font-semibold">{{ $label }}</a>
            @endforeach
        </div>
        
        <div class="flex flex-wrap gap-2">
            <!-- Form untuk Cetak Label Terpilih (Massal - Hanya untuk yang statusnya selesai) -->
            <form action="{{ route('owner.orders.print_bulk') }}" method="POST" target="_blank" id="bulkPrintForm">
                @csrf
                <template x-for="id in selectedOrders" :key="id">
                    <input type="hidden" name="order_ids[]" :value="id">
                </template>
                <button type="submit" 
                        :disabled="selectedOrders.length === 0"
                        class="inline-flex items-center gap-1.5 rounded-md bg-emerald-700 px-3 py-2 text-[10px] font-semibold text-white hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span>⎙</span> Cetak Label Terpilih (<span x-text="selectedOrders.length"></span>)
                </button>
            </form>

            <button type="button" @click="showWa = true" class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-2 text-[10px] font-semibold text-gray-600 hover:bg-gray-50">
                <span>▣</span> Dari WhatsApp
            </button>
            <button type="button" @click="openCreate()" class="inline-flex items-center gap-1.5 rounded-md bg-[#0f2137] px-3 py-2 text-[10px] font-semibold text-white hover:bg-[#183252]">
                <span>＋</span> Tambah Pesanan
            </button>
        </div>
    </div>

    <!-- Wrapper Tabel -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[1040px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-3 py-3 w-8 text-center">
                        <!-- Checkbox Select All hanya mencentang baris yang statusnya completed -->
                        <input type="checkbox" @click="toggleSelectAll($event)" class="rounded border-gray-300" title="Pilih semua yang statusnya selesai">
                    </th>
                    <th class="px-3 py-3">No. Pesanan</th>
                    <th class="px-3 py-3">Nama Pelanggan</th>
                    <th class="px-3 py-3">No. HP</th>
                    <th class="px-3 py-3">Jumlah</th>
                    <th class="px-3 py-3">Varian Rasa</th>
                    <th class="px-3 py-3">Total Harga</th>
                    <th class="px-3 py-3">Catatan</th>
                    <th class="px-3 py-3">Tgl. Ambil</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Aksi</th>
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
                    <td class="px-3 py-3 text-center">
                        @if($order->status === 'completed')
                            <input type="checkbox" value="{{ $order->id }}" x-model.number="selectedOrders" class="rounded border-gray-300">
                        @else
                            <input type="checkbox" disabled class="rounded border-gray-200 bg-gray-100 cursor-not-allowed opacity-50" title="Label hanya untuk pesanan selesai">
                        @endif
                    </td>
                    <td class="px-3 py-3 font-semibold text-[#0f2137]">P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-3 py-3">{{ $order->customer_name }}</td>
                    <td class="px-3 py-3">{{ $order->customer_phone }}</td>
                    <td class="px-3 py-3">{{ $quantity }} pcs</td>
                    <td class="px-3 py-3">{{ $flavors ?: '-' }}</td>
                    <td class="px-3 py-3 font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    <td class="max-w-[130px] truncate px-3 py-3">{{ $order->notes ?: '—' }}</td>
                    <td class="px-3 py-3">{{ \Carbon\Carbon::parse($order->pickup_at)->format('d-m-Y') }}</td>
                    <td class="px-3 py-3"><span class="{{ $statusStyles[$order->status][1] ?? 'bg-gray-100 text-gray-500' }} rounded-full px-2 py-1 text-[9px] font-semibold">{{ $statusStyles[$order->status][0] ?? $order->status }}</span></td>
                    <td class="px-3 py-3">
                        <div class="flex gap-2">
                            <!-- Tombol Ubah -->
                            <button @click="openEdit(@js($order->load('details.productVariant')))" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-50"><span>✎</span> Ubah</button>
                            
                            <!-- Tombol Hapus -->
                            <button @click="openDelete('{{ route('owner.orders.destroy', $order) }}', 'P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}')" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-[10px] font-semibold text-red-500 hover:bg-red-100"><span>🗑</span> Hapus</button>
                            
                            <!-- Tombol Label Individu (Hanya aktif jika status 'completed') -->
                            @if($order->status === 'completed')
                                <button type="button" @click="openLabel(@js($order->load('details.productVariant')), '{{ route('owner.orders.print_label', $order) }}')" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-50" title="Preview label"><span>▧</span> Label</button>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full border border-gray-100 bg-gray-50 px-3 py-1 text-[10px] text-gray-300 cursor-not-allowed" title="Label hanya untuk pesanan selesai"><span>▧</span> Label</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="px-4 py-10 text-center text-xs text-gray-400">Belum ada data pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL FORM PESANAN -->
    <div x-show="showForm" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="showForm=false" class="w-full max-w-xl rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-bold text-[#0f2137]" x-text="editing ? 'Ubah Pesanan' : 'Tambah Pesanan Baru'"></h3>
                <button @click="showForm=false" class="text-gray-400">✕</button>
            </div>
            
            <form :action="formAction" method="POST" class="space-y-4 p-5 max-h-[80vh] overflow-y-auto">
                @csrf 
                <template x-if="editing">@method('PUT')</template>
                
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-[10px] font-semibold text-gray-600">NAMA PELANGGAN *
                        <input name="customer_name" x-model="form.customer_name" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    <label class="text-[10px] font-semibold text-gray-600">NO. HP *
                        <input name="customer_phone" x-model="form.customer_phone" required 
                               type="text" maxlength="13" pattern="^08[0-9]{8,11}$" 
                               @input="form.customer_phone = form.customer_phone.replace(/[^0-9]/g, '')"
                               class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    
                    <label class="text-[10px] font-semibold text-gray-600">TANGGAL &amp; JAM AMBIL *
                        <input name="pickup_at" type="datetime-local" x-model="form.pickup_at" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    </label>
                    <label class="text-[10px] font-semibold text-gray-600">STATUS
                        <select name="status" x-model="form.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                            <option value="pending">Menunggu Produksi</option>
                            <option value="process">Sedang Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Batal</option>
                        </select>
                    </label>

                    <div class="col-span-1 sm:col-span-2 rounded-lg border border-gray-100 p-3 bg-gray-50">
                        <label class="mb-2 block text-[10px] font-semibold text-gray-600">PRODUK YANG DIPESAN *</label>
                        <template x-for="(item, index) in form.variants" :key="index">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="flex-1">
                                    <select :name="'variants[' + index + '][id]'" x-model="item.variant_id" @change="updatePrice(index)" required class="w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                                        <option value="">Pilih varian</option>
                                        @foreach($variants as $variant)
                                            <option value="{{ $variant->id }}" x-bind:disabled="isVariantDisabled({{ $variant->id }}, index)">
                                                {{ $variant->name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input :name="'variants[' + index + '][quantity]'" type="number" min="1" x-model="item.quantity" required class="w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="Qty">
                                </div>
                                <input type="hidden" :name="'variants[' + index + '][price]'" :value="item.price">
                                <div>
                                    <button type="button" @click="removeVariant(index)" x-show="form.variants.length > 1" class="rounded-md border border-red-200 bg-red-50 px-2 py-1.5 text-xs text-red-500">✕</button>
                                </div>
                            </div>
                        </template>
                        <button type="button" @click="addVariant()" x-show="canAddMoreVariants()" class="mt-1 rounded border border-gray-300 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600">＋ Tambah Varian</button>
                    </div>
                </div>
                
                <label class="block text-[10px] font-semibold text-gray-600">NOTE / CATATAN
                    <textarea name="notes" x-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></textarea>
                </label>
                
                <div class="flex gap-2 border-t border-gray-100 pt-4">
                    <button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white" x-text="editing ? 'Simpan Perubahan' : 'Simpan Pesanan'"></button>
                    <button type="button" @click="showForm=false" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL WHATSAPP PARSER -->
    <div x-show="showWa" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
            <div class="flex justify-between"><h3 class="text-sm font-bold text-[#0f2137]">Parsing dari WhatsApp</h3><button @click="showWa=false" class="text-gray-400">✕</button></div>
            <form action="{{ route('owner.orders.parse_wa') }}" method="POST" class="mt-3">
                @csrf
                <textarea name="raw_template" required rows="8" class="w-full rounded-md border border-gray-200 p-3 text-xs" placeholder="Tempel teks WhatsApp di sini..."></textarea>
                <div class="mt-3 flex gap-2">
                    <button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white">Isi Otomatis &amp; Lanjut</button>
                    <button type="button" @click="showWa=false" class="rounded-md border border-gray-200 px-4 py-2 text-xs">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div x-show="showDelete" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl">
            <h3 class="text-sm font-bold text-[#0f2137]">Hapus Pesanan?</h3>
            <p class="mt-3 text-xs text-gray-500">Yakin ingin menghapus <strong x-text="deleteName"></strong>?</p>
            <form :action="deleteAction" method="POST" class="mt-5 flex gap-2">
                @csrf @method('DELETE')
                <button class="flex-1 rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white">Ya, Hapus</button>
                <button type="button" @click="showDelete=false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs">Batal</button>
            </form>
        </div>
    </div>

    <!-- MODAL LABEL SATUAN -->
    <div x-cloak x-show="showLabel" @keydown.escape.window="showLabel=false" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="showLabel=false" class="w-full max-w-md rounded-xl bg-white p-4 shadow-xl">
            <div class="flex items-center justify-between px-2 pb-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-[#0f2137]">Preview Label Pesanan</h3>
                <button type="button" @click="showLabel=false" class="text-gray-400">✕</button>
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
                <button type="button" @click="printLabel()" class="flex-1 rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white">⎙ Cetak PDF</button>
                <button type="button" @click="showLabel=false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Tutup</button>
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
    
    // Ambil hanya ID pesanan yang statusnya 'completed' untuk fitur "Select All"
    const completedOrderIds = @json($orders->where('status', 'completed')->pluck('id')->values());

    return {
        showForm: false, showWa: false, showDelete: false, showLabel: false, editing: false, 
        formAction: '{{ route('owner.orders.store') }}', deleteAction: '', deleteName: '', 
        labelOrder: { details: [] }, labelUrl: '', 
        selectedOrders: [], 
        form: JSON.parse(JSON.stringify(empty)),

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedOrders = completedOrderIds;
            } else {
                this.selectedOrders = [];
            }
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
        printLabel() { window.open(this.labelUrl, 'labelPrintPopup', 'width=520,height=720,resizable=yes,scrollbars=yes'); },
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