@extends('layouts.app')

@section('header_title', 'Konfirmasi Pesanan WhatsApp')

@section('content')
<div class="mx-auto max-w-3xl rounded-xl border border-[#e7e0d8] bg-white p-5 shadow-sm" x-data="previewManager()">
    <h1 class="text-sm font-bold text-[#0f2137]">Preview Data Pesanan</h1>
    <p class="mt-1 text-xs text-gray-500">Periksa dan perbaiki data hasil parsing sebelum menyimpan pesanan.</p>
    
    <!-- Notifikasi Error / Sukses dari Backend (Mencegah Layar Blank) -->
    @if(session('success'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error') || $errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
            {{ session('error') ?: $errors->first() }}
        </div>
    @endif

    <form action="{{ route('owner.orders.store') }}" method="POST" class="mt-5 space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="text-[10px] font-semibold text-gray-600">NAMA PELANGGAN *
                <input name="customer_name" x-model="form.customer_name" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
            </label>
            
            <label class="text-[10px] font-semibold text-gray-600">NO. HP *
                <input name="customer_phone" x-model="form.customer_phone" required 
                       type="text" 
                       maxlength="13" 
                       pattern="^08[0-9]{8,11}$" 
                       title="Nomor HP harus diawali dengan 08 dan terdiri dari 10-13 angka"
                       @input="form.customer_phone = form.customer_phone.replace(/[^0-9]/g, '')"
                       class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                <p x-show="form.customer_phone.length > 0 && form.customer_phone.length < 10" class="mt-1 text-[9px] text-red-500">Nomor HP kurang dari 10 angka, cek kembali.</p>
            </label>
            
            <label class="text-[10px] font-semibold text-gray-600">TANGGAL &amp; JAM AMBIL *
                <input name="pickup_at" type="datetime-local" x-model="form.pickup_at" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                <p x-show="!form.pickup_at" class="mt-1 text-[9px] text-red-500">Format waktu gagal dikenali, mohon diisi manual.</p>
            </label>
            
            <label class="text-[10px] font-semibold text-gray-600">STATUS
                <select name="status" x-model="form.status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">
                    <option value="pending">Menunggu Produksi</option>
                    <option value="process">Sedang Diproses</option>
                </select>
            </label>

            <!-- BAGIAN INPUT VARIAN DINAMIS -->
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
                            <input :name="'variants[' + index + '][quantity]'" 
                                type="number" 
                                min="1" 
                                x-model="item.quantity" 
                                @keydown="if(['-', '+', 'e', 'E', '.', ','].includes($event.key)) $event.preventDefault()"
                                @input="if(item.quantity < 1 && item.quantity !== '') item.quantity = 1"
                                @blur="if(item.quantity === '' || item.quantity < 1) item.quantity = 1"
                                required 
                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-xs" 
                                placeholder="Qty">
                        </div>
                        <input type="hidden" :name="'variants[' + index + '][price]'" :value="item.price">
                        <div>
                            <button type="button" @click="removeVariant(index)" x-show="form.variants.length > 1" class="rounded-md border border-red-200 bg-red-50 px-2 py-1.5 text-xs text-red-500 hover:bg-red-100">✕</button>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addVariant()" x-show="canAddMoreVariants()" class="mt-1 rounded border border-gray-300 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-100">＋ Tambah Varian</button>
            </div>
        </div>
        
        <label class="block text-[10px] font-semibold text-gray-600 mt-2">CATATAN
            <textarea name="notes" x-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="Catatan tambahan (opsional)"></textarea>
        </label>
        
        <div class="flex gap-2 border-t border-gray-100 pt-4 mt-4">
            <button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white">Simpan Pesanan</button>
            <a href="{{ route('owner.orders.index') }}" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</div>

<script>
function previewManager() {
    const variantsData = @json($variants->keyBy('id'));
    const totalVariantChoices = Object.keys(variantsData).length;
    
    // Inject data hasil parse dari backend
    const initialData = @json($parsedData);
    
    // Looping data varian hasil parse agar terhubung ke form state
    let mappedVariants = initialData.variants.map(v => ({
        variant_id: v.id || '',
        quantity: v.quantity || 1,
        price: v.price || 0
    }));

    return {
        form: {
            customer_name: initialData.customer_name || '',
            customer_phone: initialData.customer_phone || '',
            pickup_at: initialData.pickup_at ? initialData.pickup_at.replace(' ', 'T') : '',
            status: 'pending',
            notes: initialData.notes || '',
            variants: mappedVariants.length ? mappedVariants : [{ variant_id: '', quantity: 1, price: 0 }]
        },

        addVariant() {
            this.form.variants.push({ variant_id: '', quantity: 1, price: 0 });
        },
        removeVariant(index) {
            if (this.form.variants.length > 1) {
                this.form.variants.splice(index, 1);
            }
        },
        updatePrice(index) {
            let vid = this.form.variants[index].variant_id;
            this.form.variants[index].price = (variantsData[vid] || {}).price || 0;
        },
        isVariantDisabled(variantId, currentIndex) {
            return this.form.variants.some((v, idx) => idx !== currentIndex && v.variant_id == variantId);
        },
        canAddMoreVariants() {
            return this.form.variants.length < totalVariantChoices;
        }
    }
}
</script>
@endsection