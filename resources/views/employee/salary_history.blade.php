@extends('layouts.employee')
@section('content')
<div class="min-h-screen bg-[#f0f2f5] pb-20">
    <!-- Header -->
    <div class="bg-white p-4 sm:px-6 flex items-center border-b border-gray-200 sticky top-0 z-10 shadow-sm">
        <a href="{{ route('employee.dashboard') }}" class="text-gray-500 hover:text-[#0f2137] p-2 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-base sm:text-lg font-bold ml-2 text-gray-800">Riwayat Presensi & Gaji</h1>
    </div>

    <div class="p-5 sm:px-10 md:px-16 pt-6">
        <!-- Ringkasan Gaji -->
        <div class="bg-[#0f2137] text-white p-6 sm:p-8 rounded-2xl shadow-md mb-8">
            <p class="text-[10px] sm:text-xs font-semibold text-gray-300 uppercase tracking-wide">Total Diterima Minggu Ini</p>
            <div class="text-3xl sm:text-4xl font-bold mt-2">Rp {{ number_format($salaryData['total_gaji'], 0, ',', '.') }}</div>
            <div class="mt-6 flex flex-col sm:flex-row sm:justify-between text-sm sm:text-base text-gray-300 gap-3 border-t border-white/10 pt-4">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Kehadiran: <b class="text-white">{{ $salaryData['total_kehadiran'] }} Hari</b>
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Lembur: <b class="text-white">{{ $salaryData['total_lembur'] }} Jam</b>
                </span>
            </div>
        </div>

        <!-- List Riwayat Harian -->
        <h3 class="text-xs font-bold text-gray-400 mb-4 tracking-wider uppercase">DETAIL MINGGU INI</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($salaryData['attendances'] as $att)
            <div class="bg-white p-5 rounded-xl shadow-sm border border-[#e7e0d8] hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <div class="font-bold text-gray-800 text-sm sm:text-base">{{ $att->date->translatedFormat('l, d M Y') }}</div>
                    <div class="font-bold text-[#348b66] bg-[#dff5e9] px-2 py-1 rounded-md text-xs sm:text-sm border border-[#c3ebd5]">
                        Rp {{ number_format($att->daily_wage, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="text-xs sm:text-sm text-gray-600 flex flex-col gap-2">
                    <div class="flex items-center bg-gray-50 border border-gray-100 rounded-lg p-2">
                        <svg class="w-4 h-4 mr-2 text-[#4c83b5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Masuk: <span class="ml-1 font-semibold">{{ $att->check_in_at ? $att->check_in_at->format('H:i') : '-' }}</span>
                    </div>
                    <div class="flex items-center bg-gray-50 border border-gray-100 rounded-lg p-2">
                        <svg class="w-4 h-4 mr-2 text-[#c28455]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Pulang: <span class="ml-1 font-semibold">{{ $att->check_out_at ? $att->check_out_at->format('H:i') : '-' }}</span>
                    </div>
                    @if($att->overtime_1x > 0 || $att->overtime_2x > 0)
                    <div class="flex items-center text-[#a17b1c] mt-1 font-semibold bg-[#fff6d8] p-2 rounded-lg border border-[#f5e3d3] w-max">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Lembur: {{ $att->overtime_1x + $att->overtime_2x }} Jam
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-1 md:col-span-2 text-center bg-white rounded-xl border border-gray-100 py-10 text-gray-400 text-sm">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Belum ada data presensi minggu ini.
            </div>
            @endforelse
        </div>
        
        @if(isset($salaryData['attendances']) && method_exists($salaryData['attendances'], 'hasPages') && $salaryData['attendances']->hasPages())
            <div class="mt-6">
                {{ $salaryData['attendances']->links() }}
            </div>
        @endif
    </div>
</div>
@endsection