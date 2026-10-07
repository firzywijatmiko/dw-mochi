@extends('layouts.app')

@section('header_title', 'Konfirmasi Pesanan WhatsApp')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-[#e7e0d8] bg-white p-5 shadow-sm">
    <h1 class="text-sm font-bold text-[#0f2137]">Preview Data Pesanan</h1>
    <p class="mt-1 text-xs text-gray-500">Periksa data hasil parsing sebelum menyimpan pesanan.</p>
    <form action="{{ route('owner.orders.store') }}" method="POST" class="mt-5 space-y-4">
        @csrf
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="text-[10px] font-semibold text-gray-600">NAMA PELANGGAN *<input name="customer_name" value="{{ $parsedData['customer_name'] }}" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
            <label class="text-[10px] font-semibold text-gray-600">NO. HP *<input name="customer_phone" value="{{ $parsedData['customer_phone'] }}" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
            <label class="text-[10px] font-semibold text-gray-600">JUMLAH (PCS) *<input name="variants[0][quantity]" type="number" value="1" min="1" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
            <label class="text-[10px] font-semibold text-gray-600">VARIAN RASA
                <select name="variants[0][id]" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"><option value="">Pilih varian</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->name }}</option>@endforeach</select>
            </label>
            <input type="hidden" name="variants[0][price]" value="{{ optional($variants->first())->price ?: 0 }}">
            <label class="text-[10px] font-semibold text-gray-600">TANGGAL &amp; JAM AMBIL *<input name="pickup_at" type="datetime-local" value="{{ $parsedData['pickup_at'] ? date('Y-m-d\TH:i', strtotime($parsedData['pickup_at'])) : '' }}" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"></label>
            <label class="text-[10px] font-semibold text-gray-600">STATUS<select name="status" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs"><option value="pending">Menunggu Produksi</option><option value="process">Sedang Diproses</option></select></label>
        </div>
        <label class="block text-[10px] font-semibold text-gray-600">CATATAN<textarea name="notes" rows="2" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs">{{ $parsedData['notes'] }}</textarea></label>
        <div class="flex gap-2 border-t border-gray-100 pt-4"><button class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white">Simpan Pesanan</button><a href="{{ route('owner.orders.index') }}" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</a></div>
    </form>
</div>
@endsection
