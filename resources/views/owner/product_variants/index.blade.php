<!-- resources/views/owner/product_variants/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Kelola Produk')

@section('content')
<div x-data="productVariantManager()">

    <!-- Flash Message untuk Success/Error -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Tombol Tambah Produk -->
    <div class="flex justify-end mb-4">
        <button @click="openCreateModal()" class="bg-[#0f2137] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#183252] flex items-center gap-1.5 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk
        </button>
    </div>

    <!-- Tabel Daftar Produk -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold text-[10px]">
                <tr>
                    <th class="px-6 py-4 border-b border-gray-100">Nama Produk</th>
                    <th class="px-6 py-4 border-b border-gray-100">Harga per Pcs</th>
                    <th class="px-6 py-4 border-b border-gray-100">Status</th>
                    <th class="px-6 py-4 border-b border-gray-100">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @forelse($variants as $variant)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">{{ $variant->name }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($variant->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($variant->is_active)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-md text-[10px] font-bold">Aktif</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-[10px] font-bold">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        <!-- Tombol Ubah -->
                        <button @click="openEditModal({{ $variant }})" class="border border-gray-200 text-gray-600 px-3 py-1.5 rounded-lg text-[10px] hover:bg-gray-50 flex items-center gap-1 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Ubah
                        </button>
                        <!-- Tombol Hapus -->
                        <button @click="openDeleteModal({{ $variant->id }}, '{{ addslashes($variant->name) }}')" class="border border-red-100 text-red-500 bg-red-50 px-3 py-1.5 rounded-lg text-[10px] hover:bg-red-100 flex items-center gap-1 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL FORM (TAMBAH / UBAH PRODUK) -->
    <div x-show="isFormModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div @click.away="closeFormModal()" class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden" x-transition>
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800" x-text="isEdit ? 'Ubah Data Produk' : 'Tambah Produk Baru'"></h3>
                <button @click="closeFormModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="formAction" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Nama Produk *</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none" placeholder="Contoh: Mochi Daifuku Coklat">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Harga per Pcs (Rp) *</label>
                        <!-- Input text untuk tampilan (otomatis ada titiknya) -->
                        <input type="text" x-model="formattedPrice" @input="formatPrice" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none" placeholder="1.000">
                        <!-- Input hidden untuk mengirim data berupa angka murni (integer) ke backend -->
                        <input type="hidden" name="price" x-model="formData.price">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Status</label>
                        <select name="is_active" x-model="formData.is_active" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-4">
                    <button type="submit" class="bg-[#0f2137] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#183252] transition" x-text="isEdit ? 'Simpan Perubahan' : 'Simpan'"></button>
                    <button type="button" @click="closeFormModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-show="isDeleteModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div @click.away="isDeleteModalOpen = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden p-6" x-transition>
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold text-gray-800">Hapus Produk</h3>
                <button @click="isDeleteModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <p class="text-xs text-gray-600 mb-6">Apakah Yakin Ingin Menghapus Data Ini?<br><strong x-text="deleteProductName" class="text-gray-800 block mt-1"></strong></p>
            
            <form :action="deleteActionUrl" method="POST" class="flex items-center gap-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-1/2 bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-red-700 transition flex justify-center items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Ya, Hapus
                </button>
                <button type="button" @click="isDeleteModalOpen = false" class="w-1/2 px-4 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">Batal</button>
            </form>
        </div>
    </div>

</div>

<!-- Script AlpineJS -->
<script>
    function productVariantManager() {
        return {
            isFormModalOpen: false,
            isDeleteModalOpen: false,
            isEdit: false,
            formAction: '',
            deleteActionUrl: '',
            deleteProductName: '',
            formData: {
                name: '',
                price: '',
                is_active: 1
            },
            formattedPrice: '',
            
            formatPrice(e) {
                // Hapus semua karakter yang bukan angka
                let rawValue = e.target.value.replace(/\D/g, "");
                this.formData.price = rawValue; 
                // Format angka menggunakan konfigurasi mata uang Indonesia (menambahkan pemisah titik)
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
                this.formData = { 
                    name: variant.name, 
                    price: variant.price,
                    is_active: variant.is_active
                };
                // Format langsung harga dari database ke tampilan UI
                this.formattedPrice = new Intl.NumberFormat('id-ID').format(variant.price);
                this.isFormModalOpen = true;
            },
            
            closeFormModal() {
                this.isFormModalOpen = false;
            },

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