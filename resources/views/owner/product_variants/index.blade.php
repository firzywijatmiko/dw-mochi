<!-- resources/views/owner/product_variants/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Kelola Produk')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="productVariantManager()" class="space-y-4">

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="flex justify-end">
        <button @click="openCreateModal()" class="inline-flex items-center gap-1.5 rounded-md bg-[#0f2137] px-3 py-2 text-[10px] font-semibold text-white hover:bg-[#183252]">
            <span>＋</span> Tambah Produk
        </button>
    </div>

    <!-- Tabel Daftar Produk -->
    <div class="overflow-x-auto rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <table class="min-w-[500px] w-full text-left text-[10px]">
            <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                <tr>
                    <th class="px-4 py-3 border-b border-[#e7e0d8]">Nama Produk</th>
                    <th class="px-4 py-3 border-b border-[#e7e0d8]">Harga per Pcs</th>
                    <th class="px-4 py-3 border-b border-[#e7e0d8]">Status</th>
                    <th class="px-4 py-3 border-b border-[#e7e0d8]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                @forelse($variants as $variant)
                <tr class="hover:bg-[#fcfbf9] transition">
                    <td class="px-4 py-3 font-medium text-[#0f2137]">{{ $variant->name }}</td>
                    <td class="px-4 py-3">Rp {{ number_format($variant->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @if($variant->is_active)
                            <span class="inline-flex rounded-full bg-[#dff5e9] text-[#348b66] px-2 py-0.5 text-[9px] font-semibold">Aktif</span>
                        @else
                            <span class="inline-flex rounded-full bg-[#fee8e7] text-[#bd4d48] px-2 py-0.5 text-[9px] font-semibold">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <!-- Tombol Ubah -->
                            <button @click="openEditModal({{ $variant }})" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-50">
                                <span>✎</span> Ubah
                            </button>
                            <!-- Tombol Hapus -->
                            <button @click="openDeleteModal({{ $variant->id }}, '{{ addslashes($variant->name) }}')" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-[10px] font-semibold text-red-500 hover:bg-red-100">
                                <span>🗑</span> Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL FORM (TAMBAH / UBAH PRODUK) -->
    <div x-cloak x-show="isFormModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="closeFormModal()" class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden" x-transition>
            
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-[#0f2137] text-sm" x-text="isEdit ? 'Ubah Data Produk' : 'Tambah Produk Baru'"></h3>
                <button @click="closeFormModal()" class="text-gray-400">✕</button>
            </div>

            <form :action="formAction" method="POST" class="p-5 space-y-3">
                @csrf
                <template x-if="isEdit">@method('PUT')</template>

                <label class="block text-[10px] font-semibold text-gray-600">NAMA PRODUK *
                    <input type="text" name="name" x-model="formData.name" required class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none" placeholder="Cth: Daifuku Coklat">
                </label>
                
                <label class="block text-[10px] font-semibold text-gray-600">HARGA PER PCS (RP) *
                    <input type="text" x-model="formattedPrice" @input="formatPrice" required class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none" placeholder="1.000">
                    <input type="hidden" name="price" x-model="formData.price">
                </label>
                
                <label class="block text-[10px] font-semibold text-gray-600">STATUS
                    <select name="is_active" x-model="formData.is_active" class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-[#0f2137] outline-none bg-white">
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </label>

                <div class="flex gap-2 pt-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-semibold" x-text="isEdit ? 'Simpan' : 'Simpan'"></button>
                    <button type="button" @click="closeFormModal()" class="flex-1 border border-gray-200 px-4 py-2 rounded-md text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-cloak x-show="isDeleteModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="isDeleteModalOpen = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5" x-transition>
            <h3 class="text-sm font-bold text-[#0f2137] mb-2">Hapus Produk?</h3>
            <p class="text-[10px] text-gray-500 mb-5">Apakah Yakin Ingin Menghapus <strong x-text="deleteProductName" class="text-gray-800"></strong>?</p>
            
            <form :action="deleteActionUrl" method="POST" class="flex gap-2">
                @csrf @method('DELETE')
                <button type="submit" class="flex-1 rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white">Ya, Hapus</button>
                <button type="button" @click="isDeleteModalOpen = false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</button>
            </form>
        </div>
    </div>

</div>

<!-- Script AlpineJS -->
<script>
    function productVariantManager() {
        return {
            isFormModalOpen: false, isDeleteModalOpen: false, isEdit: false,
            formAction: '', deleteActionUrl: '', deleteProductName: '',
            formData: { name: '', price: '', is_active: 1 }, formattedPrice: '',
            
            formatPrice(e) {
                let rawValue = e.target.value.replace(/\D/g, "");
                this.formData.price = rawValue; 
                this.formattedPrice = rawValue ? new Intl.NumberFormat('id-ID').format(rawValue) : '';
            },
            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('owner.product_variants.store') }}";
                this.formData = { name: '', price: '', is_active: 1 };
                this.formattedPrice = '';
                this.isFormModalOpen = true;
            },
            openEditModal(variant) {
                this.isEdit = true;
                let baseUrl = "{{ route('owner.product_variants.update', ':id') }}";
                this.formAction = baseUrl.replace(':id', variant.id);
                this.formData = { name: variant.name, price: variant.price, is_active: variant.is_active };
                this.formattedPrice = new Intl.NumberFormat('id-ID').format(variant.price);
                this.isFormModalOpen = true;
            },
            closeFormModal() { this.isFormModalOpen = false; },
            openDeleteModal(id, name) {
                let baseUrl = "{{ route('owner.product_variants.destroy', ':id') }}";
                this.deleteActionUrl = baseUrl.replace(':id', id);
                this.deleteProductName = name;
                this.isDeleteModalOpen = true;
            }
        }
    }
</script>
@endsection