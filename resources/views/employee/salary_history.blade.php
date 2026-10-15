@extends('layouts.employee')
@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white p-4 flex items-center border-b border-gray-100 sticky top-0 z-10">
        <a href="{{ route('employee.dashboard') }}" class="text-gray-500 hover:text-gray-800 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-lg font-bold ml-2 text-gray-800">Riwayat Presensi & Gaji</h1>
    </div>

    <div class="p-5">
        <!-- Ringkasan Gaji -->
        <div class="bg-[#112d4e] text-white p-5 rounded-2xl shadow-md mb-6">
            <p class="text-xs font-semibold opacity-80 uppercase tracking-wide">Total Diterima Minggu Ini</p>
            <div class="text-3xl font-bold mt-1">Rp {{ number_format($salaryData['total_gaji'], 0, ',', '.') }}</div>
            <div class="mt-4 flex justify-between text-sm opacity-90">
                <span>Kehadiran: <b>{{ $salaryData['total_kehadiran'] }} Hari</b></span>
                <span>Lembur: <b>{{ $salaryData['total_lembur'] }} Jam</b></span>
            </div>
        </div>

        <!-- List Riwayat Harian -->
        <h3 class="text-xs font-bold text-gray-400 mb-3 tracking-wider">DETAIL MINGGU INI</h3>
        
        <div class="space-y-3">
            @forelse($salaryData['attendances'] as $att)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-2">
                    <div class="font-bold text-gray-800">{{ $att->date->format('l, d M Y') }}</div>
                    <div class="font-bold text-green-600">Rp {{ number_format($att->daily_wage, 0, ',', '.') }}</div>
                </div>
                
                <div class="text-sm text-gray-500">
                    <div class="flex items-center mb-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Masuk: {{ $att->check_in_at ? $att->check_in_at->format('H:i') : '-' }}
                    </div>
                    <div class="flex items-center mb-1">
                        <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Pulang: {{ $att->check_out_at ? $att->check_out_at->format('H:i') : '-' }}
                    </div>
                    @if($att->overtime_1x > 0 || $att->overtime_2x > 0)
                    <div class="flex items-center text-blue-600 mt-2 font-medium bg-blue-50 p-1.5 rounded-md w-max">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lembur: {{ $att->overtime_1x + $att->overtime_2x }} Jam
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-gray-400 py-6 text-sm">
                Belum ada data presensi minggu ini.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection