@extends('layouts.employee')
@section('content')
<div class="min-h-screen bg-[#f0f2f5] pb-20">
    
    <!-- ================= HEADER (NAVY BLUE) ================= -->
    <div class="bg-[#0f2137] text-white p-6 rounded-b-[2.5rem] shadow-lg relative pb-16">
        
        <!-- Header Top: Profile & Logout -->
        <div class="flex justify-between items-start gap-4 mb-2">
            <!-- Info Profil -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 shrink-0 bg-white/10 rounded-full flex items-center justify-center border border-white/20">
                    <svg class="w-5 h-5 text-[#e8d5c4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Karyawan DW Mochi</p>
                    <h1 class="text-base sm:text-lg font-bold text-white leading-tight">Halo, {{ Auth::user()->name }} 👋</h1>
                </div>
            </div>
            
            <!-- Tombol Logout (Sekarang pakai Flex, tidak lagi Absolute) -->
            <form action="{{ route('logout') }}" method="POST" class="shrink-0 mt-0.5">
                @csrf
                <button type="submit" class="bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 text-red-100 text-xs font-bold py-1.5 px-3 rounded-lg flex items-center transition-all">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>
            </form>
        </div>
        
        <!-- Waktu & Tanggal Live -->
        <div class="mt-6 text-center">
            <h2 class="text-5xl font-bold tracking-wider text-white drop-shadow-md" id="live-time">00:00</h2>
            <p class="text-xs mt-2 text-[#e8d5c4] font-medium" id="live-date">Memuat tanggal...</p>
        </div>
    </div>

    <!-- ================= AREA KONTEN UTAMA ================= -->
    <div class="px-5 -mt-10 relative z-10 space-y-5">
        
        <!-- CARD PRESENSI & LOKASI -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#e7e0d8]">
            <h3 class="text-xs font-bold text-[#0f2137] mb-3 uppercase tracking-wide border-b border-gray-100 pb-2">Status Hari Ini</h3>
            
            <!-- Status Lokasi -->
            <div id="location-status" class="bg-gray-50 text-gray-600 p-3 rounded-xl flex items-center mb-4 text-xs font-medium border border-gray-200">
                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Mendeteksi lokasi Anda...
            </div>

            <!-- Logika Tombol Check-In / Check-Out -->
            @if($attendanceToday)
                <div class="flex items-center justify-between bg-[#eef5fc] border border-[#d1e3f8] p-3 rounded-xl mb-4">
                    <div class="flex items-center">
                        <span class="rounded-md bg-[#4c83b5] p-1.5 text-white mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        </span>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-500 uppercase">Waktu Masuk</p>
                            <p class="font-bold text-[#0f2137] text-sm">{{ $attendanceToday->check_in_at->format('H:i') }} WIB</p>
                        </div>
                    </div>
                    <span class="bg-[#4c83b5] text-white text-[9px] px-2 py-1 rounded-full font-bold">Tercatat</span>
                </div>
                
                @if(!$attendanceToday->check_out_at)
                    <!-- Tombol Check-Out -->
                    <button id="btn-checkout" class="w-full bg-[#c28455] hover:bg-[#a66f46] text-white font-bold py-3.5 rounded-xl shadow-sm transition duration-200 text-sm opacity-50 cursor-not-allowed flex justify-center items-center gap-2" disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Selesaikan Pekerjaan (Check-Out)
                    </button>
                @else
                    <div class="flex items-center justify-between bg-[#edf8f2] border border-[#c3ebd5] p-3 rounded-xl mb-4">
                        <div class="flex items-center">
                            <span class="rounded-md bg-[#3f9b72] p-1.5 text-white mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </span>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-500 uppercase">Waktu Pulang</p>
                                <p class="font-bold text-[#0f2137] text-sm">{{ $attendanceToday->check_out_at->format('H:i') }} WIB</p>
                            </div>
                        </div>
                        <span class="bg-[#3f9b72] text-white text-[9px] px-2 py-1 rounded-full font-bold">Tercatat</span>
                    </div>
                    <div class="text-center mt-2 text-xs text-[#3f9b72] font-semibold bg-[#edf8f2] py-2 rounded-lg border border-[#c3ebd5]">
                        Kerja bagus hari ini! Presensi telah selesai.
                    </div>
                @endif

            @else
                <!-- Tombol Check-In -->
                <button id="btn-checkin" class="w-full bg-[#0f2137] hover:bg-[#183252] text-white font-bold py-3.5 rounded-xl shadow-sm transition duration-200 text-sm opacity-50 cursor-not-allowed flex justify-center items-center gap-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Check-In Sekarang
                </button>
            @endif
        </div>

        <!-- CARD RINGKASAN GAJI -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-[#e7e0d8]">
                <div class="flex items-start justify-between mb-2">
                    <p class="text-[9px] font-bold text-gray-500 uppercase tracking-wide">Penerimaan Minggu Ini</p>
                    <span class="rounded-md bg-[#fff4e8] p-1.5 text-[#c28455]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2m0-8V7m0 1v8m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-lg font-bold text-[#0f2137]">Rp {{ number_format($salaryData['total_gaji'] ?? 0, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-[#e7e0d8] flex flex-col justify-center">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-[10px] text-gray-500 font-semibold">Kehadiran</span>
                    <span class="text-xs font-bold text-[#0f2137] bg-gray-100 px-2 py-0.5 rounded">{{ $salaryData['total_kehadiran'] ?? 0 }} Hari</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] text-gray-500 font-semibold">Total Lembur</span>
                    <span class="text-xs font-bold text-[#0f2137] bg-gray-100 px-2 py-0.5 rounded">{{ $salaryData['total_lembur'] ?? 0 }} Jam</span>
                </div>
            </div>
        </div>

        <!-- LIST RIWAYAT PRESENSI (DENGAN PAGINATION) -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#e7e0d8] overflow-hidden">
            <div class="px-5 py-4 border-b border-[#e7e0d8] flex justify-between items-center bg-[#faf9f7]">
                <h3 class="text-xs font-bold text-[#0f2137] uppercase tracking-wide">Riwayat Presensi</h3>
            </div>
            
            <div class="divide-y divide-[#f1eeeb]">
                @forelse($salaryData['attendances'] as$att)
                <div class="p-4 hover:bg-[#fcfbf9] transition">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-bold text-[#0f2137] text-sm">{{ $att->date->translatedFormat('l, d M Y') }}</div>
                        <div class="font-bold text-[#3f9b72] text-sm bg-[#edf8f2] px-2 py-0.5 rounded-md border border-[#c3ebd5]">
                            Rp {{ number_format($att->daily_wage, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-2 text-[11px] text-gray-600 font-medium">
                        <div class="flex items-center bg-gray-50 px-2 py-1 rounded border border-gray-100">
                            <svg class="w-3.5 h-3.5 mr-1 text-[#4c83b5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Check - in :  {{ $att->check_in_at ? $att->check_in_at->format('H:i') : '-' }}
                        </div>
                        <div class="flex items-center bg-gray-50 px-2 py-1 rounded border border-gray-100">
                            <svg class="w-3.5 h-3.5 mr-1 text-[#c28455]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Check - out : {{ $att->check_out_at ? $att->check_out_at->format('H:i') : '-' }}
                        </div>
                        
                        @if($att->overtime_1x > 0 or$att->overtime_2x > 0)
                        <div class="flex items-center text-[#c28455] bg-[#fff4e8] border border-[#f5e3d3] px-2 py-1 rounded">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Lembur: {{ $att->overtime_1x +$att->overtime_2x }} Jam
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-400 py-8 text-xs">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Belum ada data presensi.
                </div>
                @endforelse
            </div>
            
            <!-- Pagination Links (Memerlukan ->paginate(10) di Controller) -->
            @if(isset($salaryData['attendances']) && method_exists($salaryData['attendances'], 'hasPages') &&$salaryData['attendances']->hasPages())
                <div class="px-5 py-4 border-t border-[#e7e0d8] bg-gray-50/50">
                    {{ $salaryData['attendances']->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // --- Waktu Realtime ---
    function updateTime() {
        const now = new Date();
        document.getElementById('live-time').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('live-date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // --- Geolocation (Radius Validasi) ---
    let currentLat = null;
    let currentLng = null;
    const locStatus = document.getElementById('location-status');
    const btnCheckIn = document.getElementById('btn-checkin');
    const btnCheckOut = document.getElementById('btn-checkout');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                
                // Ubah UI Status Lokasi (Nuansa Hijau Admin)
                locStatus.className = "bg-[#edf8f2] text-[#3f9b72] border border-[#c3ebd5] p-3 rounded-xl flex items-center mb-4 text-xs font-semibold";
                locStatus.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Lokasi Anda Terdeteksi`;

                // Aktifkan Tombol
                if(btnCheckIn) { btnCheckIn.disabled = false; btnCheckIn.classList.remove('opacity-50', 'cursor-not-allowed'); }
                if(btnCheckOut) { btnCheckOut.disabled = false; btnCheckOut.classList.remove('opacity-50', 'cursor-not-allowed'); }
            },
            (error) => {
                // Nuansa Merah
                locStatus.className = "bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl flex items-center mb-4 text-xs font-semibold";
                locStatus.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Gagal mendapat lokasi. Izinkan akses GPS Anda.`;
            },
            { enableHighAccuracy: true }
        );
    } else {
        locStatus.innerHTML = "Browser tidak mendukung Geolocation.";
    }

    // --- Aksi Check-In ---
    if(btnCheckIn) {
        btnCheckIn.addEventListener('click', function() {
            if(!currentLat) return alert("Lokasi belum terdeteksi!");
            btnCheckIn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Memproses...`;
            btnCheckIn.disabled = true;

            axios.post('{{ route('employee.attendances.check_in') }}', {
                latitude: currentLat,
                longitude: currentLng
            })
            .then(res => {
                window.location.reload();
            })
            .catch(err => {
                btnCheckIn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg> Check-In Sekarang`;
                btnCheckIn.disabled = false;
                let errMsg = err.response?.data?.error ? err.response.data.error : "Terjadi kesalahan sistem!";
                alert(errMsg);
            });
        });
    }

    // --- Aksi Check-Out ---
    if(btnCheckOut) {
        btnCheckOut.addEventListener('click', function() {
            if(!currentLat) return alert("Lokasi belum terdeteksi!");
            btnCheckOut.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Memproses...`;
            btnCheckOut.disabled = true;

            axios.post('{{ route('employee.attendances.check_out') }}', {
                latitude: currentLat,
                longitude: currentLng
            })
            .then(res => {
                window.location.reload();
            })
            .catch(err => {
                btnCheckOut.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Selesaikan Pekerjaan (Check-Out)`;
                btnCheckOut.disabled = false;
                let errMsg = err.response?.data?.error ? err.response.data.error : "Terjadi kesalahan sistem!";
                alert(errMsg);
            });
        });
    }
</script>
@endsection