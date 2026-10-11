<!-- resources/views/owner/payrolls/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Presensi & Penggajian')

@section('content')
<div class="space-y-6">

    @include('owner.partials.payroll_tabs')

    <!-- Flash Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ $errors->first() }}</div>
    @endif

    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Total Gaji Belum Dibayar</p>
            <h3 class="text-lg font-bold text-red-500">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">
                {{ $employeeId ? 'Total Riwayat Karyawan' : 'Total Gaji Periode Ini' }}
            </p>
            <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($payrolls->sum('total_pay'), 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Data Ditampilkan</p>
            <h3 class="text-lg font-bold text-gray-800">{{ $payrolls->count() }} <span class="text-xs font-medium text-gray-500">Data</span></h3>
        </div>
    </div>

    <!-- Filter + Hitung Gaji -->
    <div class="flex flex-wrap items-end justify-between gap-3">
        <form method="GET" action="{{ route('owner.payrolls.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Tanggal (dalam periode)</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-[#0f2137] outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-600 uppercase mb-1">Karyawan (riwayat)</label>
                <select name="employee_id" class="px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white focus:ring-1 focus:ring-[#0f2137] outline-none">
                    <option value="">Semua karyawan (per periode)</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ (string) $employeeId === (string) $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-[#0f2137] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#183252] transition">Tampilkan</button>
        </form>

        <form method="POST" action="{{ route('owner.payrolls.generate') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <button type="submit" class="bg-[#c28455] text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-[#a96f45] transition shadow-sm">
                Hitung Gaji Periode {{ $start->translatedFormat('d M') }} - {{ $end->translatedFormat('d M Y') }}
            </button>
        </form>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            @if($employeeId)
                <h3 class="font-bold text-gray-800 text-sm">Riwayat Gaji Karyawan</h3>
            @else
                <h3 class="font-bold text-gray-800 text-sm">Penggajian Periode {{ $start->translatedFormat('d M Y') }} - {{ $end->translatedFormat('d M Y') }}</h3>
            @endif
        </div>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold text-[10px]">
                <tr>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4">Hari Hadir</th>
                    <th class="px-6 py-4">Gaji Pokok</th>
                    <th class="px-6 py-4">Uang Makan</th>
                    <th class="px-6 py-4">Lembur</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                @forelse($payrolls as $p)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">{{ $p->employee->name ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $p->period_start->translatedFormat('d M') }} - {{ $p->period_end->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4">{{ $p->days_present }} hari</td>
                    <td class="px-6 py-4">Rp {{ number_format($p->base_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($p->meal_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">Rp {{ number_format($p->total_pay, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] rounded-full {{ $p->status === 'Dibayar' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                            {{ $p->status }}
                        </span>
                        @if(abs($p->difference) >= 1)
                            <p class="mt-1 text-[10px] text-amber-600">
                                Presensi kini Rp {{ number_format($p->current_total, 0, ',', '.') }}
                                (selisih Rp {{ number_format(abs($p->difference), 0, ',', '.') }})
                            </p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($p->status !== 'Dibayar')
                            <form method="POST" action="{{ route('owner.payrolls.pay', $p->id) }}" onsubmit="return confirm('Tandai gaji ini sudah dibayar? Data tidak akan ikut dihitung ulang.')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="bg-[#c28455] text-white px-3 py-1.5 rounded-lg text-[10px] font-medium hover:bg-[#a96f45] transition shadow-sm">Tandai Dibayar</button>
                            </form>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400">{{ optional($p->paid_at)->translatedFormat('d M Y') }}</span>
                                <form method="POST" action="{{ route('owner.payrolls.unlock', $p->id) }}" onsubmit="return confirm('Buka kunci gaji ini? Status kembali ke Belum Dibayar dan bisa dihitung ulang dari presensi.')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="bg-[#c28455] text-white px-2.5 py-1 rounded-lg text-[10px] font-medium hover:bg-[#a96f45] transition shadow-sm">Buka Kunci</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                        Belum ada data gaji. Klik tombol "Hitung Gaji" untuk membuatnya dari data presensi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection