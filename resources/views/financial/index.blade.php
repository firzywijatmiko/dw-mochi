@extends('layouts.app')

@section('header_title', 'Keuangan & Laporan')

@section('content')
<div x-data="{ openModal: false, openEditModal: false, openDeleteModal: false, deleteId: '', deleteItemName: '', editId: '', editItemName: '', editAmount: '', editDate: '', editNotes: '' }">

    <div class="space-y-6">
        <!-- Notifikasi Sukses Jika Ada -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Ringkasan (Otomatis dari Controller jika menggunakan variabel $totalIncome, $totalExpense, $netProfit) -->
        <div class="grid grid-cols-3 gap-6">
            <div class="bg-[#0a192f] text-white p-6 rounded-xl shadow">
                <p class="text-xs text-gray-400 uppercase">Total Pemasukan</p>
                <h3 class="text-2xl font-bold mt-1">Rp {{ isset($totalIncome) ? number_format($totalIncome, 0, ',', '.') : '9.450.000' }}</h3>
            </div>
            <div class="bg-white text-gray-800 p-6 rounded-xl shadow border">
                <p class="text-xs text-gray-500 uppercase">Total Pengeluaran</p>
                <h3 class="text-2xl font-bold mt-1">Rp {{ isset($totalExpense) ? number_format($totalExpense, 0, ',', '.') : '732.000' }}</h3>
            </div>
            <div class="bg-white text-gray-800 p-6 rounded-xl shadow border">
                <p class="text-xs text-gray-500 uppercase">Keuntungan Bersih</p>
                <h3 class="text-2xl font-bold mt-1">Rp {{ isset($netProfit) ? number_format($netProfit, 0, ',', '.') : '8.718.000' }}</h3>
            </div>
        </div>

        <!-- Grafik Tren Pemasukan & Pengeluaran (Mingguan) - belum tersambung ke data asli -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-bold text-gray-700 mb-4">Tren Pemasukan & Pengeluaran (Mingguan)</h3>
            <div class="h-48 flex flex-col items-center justify-center text-gray-400 border border-dashed border-gray-200 rounded-lg">
                <p class="text-sm font-medium">Grafik belum tersedia</p>
                <p class="text-xs">Akan ditampilkan setelah tersambung ke data pemasukan dan pengeluaran.</p>
            </div>
        </div>

        <!-- Tabel Catatan Pengeluaran -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-700">Catatan Pengeluaran</h3>
                <button @click="openModal = true" class="bg-[#0a192f] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#132d53] transition">
                    + Catat Pengeluaran
                </button>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-xs text-gray-400 uppercase">
                        <th class="py-3 px-4">Nama Item</th>
                        <th class="py-3 px-4">Nominal</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-600">
                    @forelse($expenses as $item)
                    @php
                        $primaryKey = array_values((array)$item)[0];
                    @endphp
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $item->item_name }}</td>
                        <td class="py-3 px-4">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">{{ $item->expense_date }}</td>
                        <td class="py-3 px-4">{{ $item->notes ?? '-' }}</td>
                        <td class="py-3 px-4 text-center space-x-2">
                            <!-- Tombol Edit -->
                            <button @click="
                                openEditModal = true; 
                                editId = '{{ $primaryKey }}'; 
                                editItemName = '{{ $item->item_name }}'; 
                                editAmount = '{{ $item->amount }}'; 
                                editDate = '{{ $item->expense_date }}'; 
                                editNotes = '{{ $item->notes ?? '' }}';
                            " class="text-blue-600 hover:text-blue-800 font-medium" title="Edit">✏️ Edit</button>

                            <!-- Tombol Hapus -->
                            <button @click="
                                openDeleteModal = true;
                                deleteId = '{{ $primaryKey }}';
                                deleteItemName = '{{ $item->item_name }}';
                            " class="text-red-600 hover:text-red-800 font-medium" title="Hapus">🗑️ Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400">Belum ada catatan pengeluaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL / POP-UP TAMBAH PENGELUARAN -->
    <div x-show="openModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative" @click.away="openModal = false">
            <button @click="openModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-lg font-bold">×</button>

            <h3 class="text-lg font-bold text-gray-800 mb-4">Catat Pengeluaran Baru</h3>

            <form action="{{ route('financial.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Item / Keperluan *</label>
                        <input type="text" name="item_name" required placeholder="cth: Bahan baku tepung" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nominal (Rp) *</label>
                        <input type="number" name="amount" required placeholder="0" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tanggal *</label>
                        <input type="date" name="expense_date" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Keterangan</label>
                        <input type="text" name="notes" placeholder="Opsional" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="openModal = false" class="border px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="bg-[#0a192f] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#132d53]">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL / POP-UP EDIT PENGELUARAN -->
    <div x-show="openEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative" @click.away="openEditModal = false">
            <button @click="openEditModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-lg font-bold">×</button>

            <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Catatan Pengeluaran</h3>

            <form :action="'/keuangan/update/' + editId" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nama Item / Keperluan *</label>
                        <input type="text" name="item_name" x-model="editItemName" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nominal (Rp) *</label>
                        <input type="number" name="amount" x-model="editAmount" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tanggal *</label>
                        <input type="date" name="expense_date" x-model="editDate" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Keterangan</label>
                        <input type="text" name="notes" x-model="editNotes" placeholder="Opsional" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0a192f]">
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="openEditModal = false" class="border px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="bg-[#0a192f] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#132d53]">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL / POP-UP KONFIRMASI HAPUS -->
    <div x-show="openDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 relative text-center" @click.away="openDeleteModal = false">
            <button @click="openDeleteModal = false" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-lg font-bold">×</button>

            <h3 class="text-base font-bold text-gray-800 mb-3">Hapus Catatan?</h3>
            <p class="text-xs text-gray-600 mb-6 leading-relaxed">
                Yakin ingin menghapus catatan <strong class="text-gray-900" x-text="deleteItemName"></strong>? Tindakan ini tidak bisa dibatalkan.
            </p>

            <div class="flex space-x-3">
                <button type="button" @click="openDeleteModal = false" class="flex-1 border border-gray-300 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>

                <form :action="'/keuangan/delete/' + deleteId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-2 rounded-lg text-xs font-semibold shadow">🗑️ Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection