<!-- resources/views/owner/financial/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Keuangan & Laporan')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="{ openModal: false, openEditModal: false, openDeleteModal: false, deleteId: '', deleteItemName: '', editId: '', editItemName: '', editAmount: '', editDate: '', editNotes: '' }" class="space-y-4">

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <!-- Card Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-[#0f2137] text-white p-4 rounded-lg shadow-sm">
            <p class="text-[9px] text-gray-300 uppercase tracking-wider mb-0.5">Total Pemasukan</p>
            <h3 class="text-base font-bold">Rp {{ isset($totalIncome) ? number_format($totalIncome, 0, ',', '.') : '0' }}</h3>
        </div>
        <div class="bg-white text-gray-800 p-4 rounded-lg shadow-sm border border-[#e7e0d8]">
            <p class="text-[9px] text-gray-500 uppercase tracking-wider mb-0.5">Total Pengeluaran</p>
            <h3 class="text-base font-bold">Rp {{ isset($totalExpense) ? number_format($totalExpense, 0, ',', '.') : '0' }}</h3>
            <p class="text-[8px] text-gray-400 mt-1">Bahan baku + gaji karyawan</p>
        </div>
        <div class="bg-white text-gray-800 p-4 rounded-lg shadow-sm border border-[#e7e0d8]">
            <p class="text-[9px] text-gray-500 uppercase tracking-wider mb-0.5">Keuntungan Bersih</p>
            <h3 class="text-base font-bold {{ (isset($netProfit) && $netProfit >= 0) ? 'text-[#348b66]' : 'text-[#bd4d48]' }}">
                Rp {{ isset($netProfit) ? number_format($netProfit, 0, ',', '.') : '0' }}
            </h3>
        </div>
    </div>

    <!-- Grafik Tren Mingguan -->
    @php
        $short = function ($n) {
            if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 2, ',', '.'), '0'), ',') . 'jt';
            if ($n > 0) return rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'rb';
            return '0';
        };
        $hasChartData = collect($chartData ?? [])->contains(function ($w) { return $w['income'] > 0 || $w['expense'] > 0; });
    @endphp
    <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] p-4">
        <h3 class="font-bold text-[#0f2137] text-sm mb-4">Tren Mingguan</h3>
        <div class="flex overflow-x-auto pb-2">
            <div class="min-w-[400px] w-full flex">
                <div class="flex flex-col justify-between text-[9px] text-gray-400 pr-2 h-40 text-right select-none w-10">
                    <span>{{ isset($chartMax) ? $short($chartMax) : '0' }}</span>
                    <span>{{ isset($chartMax) ? $short($chartMax * 0.5) : '0' }}</span>
                    <span>0</span>
                </div>
                <div class="flex-1 relative">
                    <div class="absolute inset-0 border-b border-l border-gray-200"></div>
                    <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-gray-100"></div>
                    <div class="relative h-40 flex items-end justify-around px-2">
                        @foreach($chartData ?? [] as $w)
                            @php
                                $incomePct  = (isset($chartMax) && $chartMax > 0) ? ($w['income']  / $chartMax * 100) : 0;
                                $expensePct = (isset($chartMax) && $chartMax > 0) ? ($w['expense'] / $chartMax * 100) : 0;
                            @endphp
                            <div class="flex-1 h-full flex items-end justify-center gap-1 z-10 group">
                                <div class="w-3 sm:w-5 bg-[#0f2137] rounded-t relative" style="height: {{ max($incomePct, 1) }}%;" title="Pemasukan: Rp {{ number_format($w['income'], 0, ',', '.') }}"></div>
                                <div class="w-3 sm:w-5 bg-[#c28455] rounded-t relative" style="height: {{ max($expensePct, 1) }}%;" title="Pengeluaran: Rp {{ number_format($w['expense'], 0, ',', '.') }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-around px-2 mt-1">
                        @foreach($chartData ?? [] as $w)
                            <span class="flex-1 text-center text-[8px] sm:text-[9px] text-gray-500 truncate px-1">{{ $w['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @unless($hasChartData)
            <p class="text-center text-[10px] text-gray-400 mt-2">Belum ada data 5 minggu terakhir.</p>
        @endunless
        <div class="flex justify-center items-center space-x-4 mt-3">
            <div class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 bg-[#0f2137] rounded-sm"></span><span class="text-[9px] text-gray-600">Pemasukan</span></div>
            <div class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 bg-[#c28455] rounded-sm"></span><span class="text-[9px] text-gray-600">Pengeluaran</span></div>
        </div>
    </div>

    <!-- Tabel Catatan Pengeluaran -->
    <div class="bg-white rounded-lg shadow-sm border border-[#e7e0d8] overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex flex-wrap justify-between items-center gap-2">
            <h3 class="font-bold text-[#0f2137] text-sm">Catatan Pengeluaran</h3>
            <button @click="openModal = true" class="inline-flex items-center gap-1.5 bg-[#0f2137] text-white px-3 py-1.5 rounded-md text-[10px] font-semibold hover:bg-[#183252]">
                <span>＋</span> Catat Pengeluaran
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[600px] w-full text-left text-[10px]">
                <thead class="bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Nama Item</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                    @forelse($expenses ?? [] as $item)
                    @php $primaryKey = array_values((array)$item)[0]; @endphp
                    <tr class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-3">{{ $item->item_name }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($item->expense_date)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 max-w-[150px] truncate">{{ $item->notes ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="openEditModal = true; editId = '{{ $primaryKey }}'; editItemName = '{{ $item->item_name }}'; editAmount = '{{ $item->amount }}'; editDate = '{{ $item->expense_date }}'; editNotes = '{{ $item->notes ?? '' }}';" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-50"><span>✎</span> Ubah</button>
                                <button @click="openDeleteModal = true; deleteId = '{{ $primaryKey }}'; deleteItemName = '{{ $item->item_name }}';" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-[10px] font-semibold text-red-500 hover:bg-red-100"><span>🗑</span> Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada catatan pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH PENGELUARAN -->
    <div x-cloak x-show="openModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative" @click.away="openModal = false">
            <div class="flex justify-between border-b border-gray-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-[#0f2137]">Catat Pengeluaran Baru</h3>
                <button @click="openModal = false" class="text-gray-400">✕</button>
            </div>
            <form action="{{ route('owner.financial.store') }}" method="POST" class="space-y-3">
                @csrf
                <label class="block text-[10px] font-semibold text-gray-600">NAMA ITEM / KEPERLUAN *
                    <input type="text" name="item_name" required placeholder="cth: Bahan baku tepung" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">NOMINAL (RP) *
                    <input type="number" name="amount" required placeholder="0" min="0" onkeydown="if (['-', '+', 'e', 'E'].includes(event.key)) event.preventDefault();" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">TANGGAL *
                    <input type="date" name="expense_date" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">KETERANGAN
                    <input type="text" name="notes" placeholder="Opsional" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-semibold">Simpan</button>
                    <button type="button" @click="openModal = false" class="flex-1 border border-gray-200 px-4 py-2 rounded-md text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PENGELUARAN -->
    <div x-cloak x-show="openEditModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative" @click.away="openEditModal = false">
            <div class="flex justify-between border-b border-gray-100 pb-3 mb-3">
                <h3 class="text-sm font-bold text-[#0f2137]">Ubah Catatan Pengeluaran</h3>
                <button @click="openEditModal = false" class="text-gray-400">✕</button>
            </div>
            <form :action="'{{ url('/owner/keuangan/update') }}/' + editId" method="POST" class="space-y-3">
                @csrf @method('PUT')
                <label class="block text-[10px] font-semibold text-gray-600">NAMA ITEM / KEPERLUAN *
                    <input type="text" name="item_name" x-model="editItemName" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">NOMINAL (RP) *
                    <input type="number" name="amount" x-model="editAmount" required min="0" onkeydown="if (['-', '+', 'e', 'E'].includes(event.key)) event.preventDefault();" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">TANGGAL *
                    <input type="date" name="expense_date" x-model="editDate" required class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <label class="block text-[10px] font-semibold text-gray-600">KETERANGAN
                    <input type="text" name="notes" x-model="editNotes" placeholder="Opsional" class="mt-1 w-full border border-gray-200 rounded-md px-3 py-2 text-xs">
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-[#0f2137] text-white px-4 py-2 rounded-md text-xs font-semibold">Simpan</button>
                    <button type="button" @click="openEditModal = false" class="flex-1 border border-gray-200 px-4 py-2 rounded-md text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-cloak x-show="openDeleteModal" class="fixed inset-0 bg-[#0f2137]/40 flex items-center justify-center z-50 p-4 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-5 relative text-center" @click.away="openDeleteModal = false">
            <h3 class="text-sm font-bold text-[#0f2137] mb-2">Hapus Catatan?</h3>
            <p class="text-[10px] text-gray-600 mb-5">Yakin ingin menghapus catatan <strong x-text="deleteItemName"></strong>?</p>
            <div class="flex gap-2">
                <form :action="'{{ url('/owner/keuangan/delete') }}/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-md text-xs font-semibold">Ya, Hapus</button>
                </form>
                <button type="button" @click="openDeleteModal = false" class="flex-1 border border-gray-200 py-2 rounded-md text-xs font-semibold text-gray-600">Batal</button>
            </div>
        </div>
    </div>

</div>
@endsection