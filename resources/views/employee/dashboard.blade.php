@extends('layouts.employee')
@section('content')
<div class="min-h-screen bg-[#f0f2f5] pb-20">
    
    <!-- ================= HEADER (NAVY BLUE) ================= -->
    <div class="bg-[#0f2137] text-white p-6 sm:p-8 md:p-10 rounded-b-[2.5rem] shadow-lg relative pb-16 md:pb-20">
        
        <!-- Header Top: Profile & Logout -->
        <div class="flex justify-between items-start gap-4 mb-2">
            <!-- Info Profil -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 shrink-0 bg-white/10 rounded-full flex items-center justify-center border border-white/20">
                    <svg class="w-5 h-5 text-[#e8d5c4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-400 uppercase tracking-wider">Karyawan DW Mochi</p>
                    <h1 class="text-base sm:text-lg font-bold text-white leading-tight">Halo, {{ Auth::user()->name }} 👋</h1>
                </div>
            </div>
            
            <!-- Tombol Logout Memicu Modal di AlpineJS -->
            <button @click="showLogoutModal = true" type="button" class="shrink-0 mt-0.5 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 text-red-100 text-xs font-bold py-1.5 px-3 rounded-lg flex items-center transition-all cursor-pointer">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar
            </button>
        </div>
        
        <!-- Waktu & Tanggal Live -->
        <div class="mt-6 sm:mt-8 text-center">
            <h2 class="text-5xl sm:text-6xl font-bold tracking-wider text-white drop-shadow-md" id="live-time">00:00</h2>
            <p class="text-xs sm:text-sm mt-2 text-[#e8d5c4] font-medium" id="live-date">Memuat tanggal...</p>
        </div>
    </div>

    <!-- ================= AREA KONTEN UTAMA ================= -->
    <div class="px-5 sm:px-10 md:px-16 -mt-10 relative z-10 space-y-5">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- CARD PRESENSI & LOKASI -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#e7e0d8] h-full">
                <h3 class="text-xs font-bold text-[#0f2137] mb-3 uppercase tracking-wide border-b border-gray-100 pb-2">Status Hari Ini</h3>
                
                <!-- Status Lokasi -->
                <div id="location-status" class="bg-gray-50 text-gray-600 p-3 rounded-xl flex items-center mb-4 text-xs font-medium border border-gray-200">
                    <svg class="w-4 h-4 mr-2 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Mendeteksi lokasi Anda...</span>
                </div>

                @if(session('success'))
                    <div class="bg-[#edf8f2] text-[#3f9b72] border border-[#c3ebd5] p-3 rounded-xl mb-4 text-xs font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->has('sick_leave'))
                    <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl mb-4 text-xs font-semibold">
                        {{ $errors->first('sick_leave') }}
                    </div>
                @endif

                <!-- Logika Tombol Check-In / Check-Out -->
                @if($attendanceToday)
                    @if($attendanceToday->attendance_status !== 'hadir')
                        <div class="bg-[#fff4e8] border border-[#f5e3d3] p-3 rounded-xl text-sm">
                            <p class="font-bold {{ $attendanceToday->attendance_status === 'alpa' ? 'text-red-500' : 'text-[#c28455]' }}">
                                {{ $attendanceToday->attendance_status === 'alpa' ? 'Alpa / Tanpa Keterangan' : 'Izin sakit ' . ($attendanceToday->sick_leave_status === 'approved' ? 'disetujui' : 'menunggu persetujuan') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $attendanceToday->attendance_status === 'alpa' ? 'Pengajuan izin ditolak oleh Owner.' : 'Bukti foto sudah dikirim.' }}
                            </p>
                        </div>
                    @else
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
                    @endif

                @else
                    <!-- Tombol Check-In -->
                    <button id="btn-checkin" class="w-full bg-[#0f2137] hover:bg-[#183252] text-white font-bold py-3.5 rounded-xl shadow-sm transition duration-200 text-sm opacity-50 cursor-not-allowed flex justify-center items-center gap-2" disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Check-In Sekarang
                    </button>
                    <form action="{{ route('employee.attendances.sick_leave') }}" method="POST" enctype="multipart/form-data" class="mt-4 border-t border-gray-100 pt-4">
                        @csrf
                        <label for="sick_leave_proof" class="block text-xs font-bold text-[#0f2137] mb-2">Ajukan izin sakit</label>
                        <input id="sick_leave_proof" name="sick_leave_proof" type="file" accept="image/jpeg,image/png,image/webp" required
                               class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700">
                        <p class="text-[10px] text-gray-400 mt-1">Unggah foto bukti sakit, maksimal 5 MB. Izin dapat diajukan dari mana saja.</p>
                        <button type="submit" class="w-full mt-3 bg-[#c28455] hover:bg-[#a66f46] text-white font-bold py-2.5 rounded-xl text-xs transition">
                            Kirim Izin Sakit
                        </button>
                    </form>
                @endif
            </div>

            <!-- CARD RINGKASAN GAJI & AKTIVITAS MINGGU INI -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-[#e7e0d8] h-full flex flex-col justify-between">
                <div>
                    <!-- Header Card -->
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <h3 class="text-xs font-bold text-[#0f2137] uppercase tracking-wide">Ringkasan Minggu Ini</h3>
                        <span class="text-[10px] font-bold text-[#c28455] bg-[#fff4e8] border border-[#f5e3d3] px-2.5 py-0.5 rounded-full">
                            {{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->translatedFormat('d M') }} – {{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(5)->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    <!-- Highlight Penerimaan Minggu Ini -->
                    <div class="bg-gradient-to-br from-[#0f2137] to-[#1a3556] text-white rounded-xl p-4 sm:p-5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold text-[#e8d5c4] uppercase tracking-wider">Estimasi Penerimaan Minggu Ini</p>
                            <span class="rounded-lg bg-white/10 p-1.5 text-[#e8d5c4]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2m0-8V7m0 1v8m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                        </div>
                        <p class="text-2xl sm:text-3xl font-bold tracking-tight mt-2">
                            Rp {{ number_format($salaryData['total_gaji'] ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-gray-300 mt-1">Akumulasi upah harian, uang makan & lembur</p>
                    </div>

                    <!-- 2 Kotak Statistik: Kehadiran & Lembur -->
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="bg-[#faf9f7] border border-[#e7e0d8] rounded-xl p-3.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#eef5fc] border border-[#d1e3f8] text-[#4c83b5] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Kehadiran</p>
                                <p class="text-base sm:text-lg font-bold text-[#0f2137] leading-tight">{{ $salaryData['total_kehadiran'] ?? 0 }} <span class="text-xs font-semibold text-gray-500">Hari</span></p>
                            </div>
                        </div>

                        <div class="bg-[#faf9f7] border border-[#e7e0d8] rounded-xl p-3.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-[#fff4e8] border border-[#f5e3d3] text-[#c28455] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Total Lembur</p>
                                <p class="text-base sm:text-lg font-bold text-[#0f2137] leading-tight">{{ $salaryData['total_lembur'] ?? 0 }} <span class="text-xs font-semibold text-gray-500">Jam</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Menuju Rekap Mingguan & Slip Gaji -->
                <a href="{{ route('employee.salary_history') }}"
                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#dce4eb] bg-[#f4f7fa] px-4 py-3 text-xs font-bold text-[#0f2137] transition hover:border-[#b9cad8] hover:bg-[#e8eff5]">
                    <svg class="h-4 w-4 text-[#0f2137]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path>
                    </svg>
                    Lihat Rekap & Slip Gaji Mingguan
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('live-time').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('live-date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    setInterval(updateTime, 1000);
    updateTime();

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
                locStatus.className = "bg-[#edf8f2] text-[#3f9b72] border border-[#c3ebd5] p-3 rounded-xl flex items-center mb-4 text-xs font-semibold";
                locStatus.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> <span>Lokasi Anda Terdeteksi</span>`;

                if(btnCheckIn) { btnCheckIn.disabled = false; btnCheckIn.classList.remove('opacity-50', 'cursor-not-allowed'); }
                if(btnCheckOut) { btnCheckOut.disabled = false; btnCheckOut.classList.remove('opacity-50', 'cursor-not-allowed'); }
            },
            (error) => {
                locStatus.className = "bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl flex items-center mb-4 text-xs font-semibold";
                locStatus.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> <span>Gagal mendapat lokasi. Izinkan akses GPS Anda.</span>`;
            },
            { enableHighAccuracy: true }
        );
    } else {
        locStatus.innerHTML = "Browser tidak mendukung Geolocation.";
    }

    if(btnCheckIn) {
        btnCheckIn.addEventListener('click', function() {
            if(!currentLat) return alert("Lokasi belum terdeteksi!");
            btnCheckIn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Memproses...`;
            btnCheckIn.disabled = true;

            axios.post('{{ route('employee.attendances.check_in') }}', { latitude: currentLat, longitude: currentLng })
            .then(res => { window.location.reload(); })
            .catch(err => {
                btnCheckIn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg> Check-In Sekarang`;
                btnCheckIn.disabled = false;
                alert(err.response?.data?.error ? err.response.data.error : "Terjadi kesalahan sistem!");
            });
        });
    }

    if(btnCheckOut) {
        btnCheckOut.addEventListener('click', function() {
            if(!currentLat) return alert("Lokasi belum terdeteksi!");
            btnCheckOut.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Memproses...`;
            btnCheckOut.disabled = true;

            axios.post('{{ route('employee.attendances.check_out') }}', { latitude: currentLat, longitude: currentLng })
            .then(res => { window.location.reload(); })
            .catch(err => {
                btnCheckOut.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Selesaikan Pekerjaan (Check-Out)`;
                btnCheckOut.disabled = false;
                alert(err.response?.data?.error ? err.response.data.error : "Terjadi kesalahan sistem!");
            });
        });
    }
</script>
@endsection