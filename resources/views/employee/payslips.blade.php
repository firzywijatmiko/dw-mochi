@extends('layouts.employee')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-lg font-bold mb-4">Slip Gaji</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-sm">{{ $errors->first() }}</div>
    @endif

    @forelse ($payslips as $p)
        <div class="bg-white rounded-xl shadow p-4 mb-3 flex justify-between items-center">
            <div>
                <p class="font-semibold">{{ $p->period_start->format('d M Y') }} - {{ $p->period_end->format('d M Y') }}</p>
                <p class="text-xs text-gray-500">Dibayar {{ $p->paid_at->format('d M Y') }}</p>
                <p class="text-sm">Rp{{ number_format($p->total_pay, 0, ',', '.') }}</p>
            </div>
            @if ($p->isPrintable())
                <a href="{{ route('employee.payslips.show', $p) }}" class="text-sm text-blue-600">Lihat dan cetak</a>
            @else
                <span class="text-xs text-gray-400">Batas cetak terlewati</span>
            @endif
        </div>
    @empty
        <p class="text-gray-400 text-sm">Belum ada slip gaji.</p>
    @endforelse
</div>
@endsection