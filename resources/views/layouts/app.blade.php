<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DW Mochi - Manajemen Operasional</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js untuk mengatur modal/popup interaktif -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f0f2f5] text-gray-800">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-[#0f2137] text-white flex flex-col justify-between h-full">
            <div>
                <!-- Bagian Logo DW Mochi -->
                <div class="flex items-center gap-3 px-6 py-6 border-b border-gray-700/50">
                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 11-10 10 1 1 0 0110-10zm0 0v2m0 16v2m8-10h2M2 12h2m13.95-7.05l-1.41 1.41M5.46 18.54l-1.41 1.41M18.54 18.54l-1.41-1.41M5.46 5.46l1.41 1.41"></path></svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-sm">DW Mochi</h1>
                        <p class="text-[10px] text-gray-400">Sistem Manajemen</p>
                    </div>
                </div>

                <!-- Menu Navigasi -->
                <nav class="mt-6 px-4 space-y-1">
                    
                    <!-- Menu Dashboard -->
                    <a href="{{ route('owner.dashboard') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.dashboard') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    
                    <!-- Menu Pesanan -->
                    <a href="{{ route('owner.orders.index') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.orders.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Pesanan
                    </a>

                    <!-- Menu Produk -->
                    <a href="{{ route('owner.product_variants.index') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.product_variants.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Produk
                    </a>

                    <!-- Menu Karyawan -->
                    <a href="{{ route('owner.employees.index') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.employees.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Karyawan
                    </a>
                    
                    <!-- Menu Presensi & Penggajian -->
                    <a href="{{ route('owner.attendances.recap') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.attendances.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Presensi & Penggajian
                    </a>

                    <!-- Menu Keuangan -->
                    <a href="{{ route('owner.expenses.index') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.expenses.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Keuangan
                    </a>

                    <!-- Menu Laporan -->
                    <a href="{{ route('owner.reports.weekly') }}" 
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('owner.reports.*') ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Laporan
                    </a>
                </nav>
            </div>

            <!-- Bagian Bawah: Tombol Logout (Dengan Konfirmasi) -->
            <div class="p-4" x-data="{ showLogoutModal: false }">
                <button @click="showLogoutModal = true" type="button" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>

                <!-- Modal Konfirmasi Logout -->
                <div x-show="showLogoutModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div @click.away="showLogoutModal = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden p-6 text-gray-800" x-transition>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <div class="p-3 bg-red-50 text-red-500 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Keluar Sistem?</h3>
                                <p class="text-xs text-gray-500">Anda harus login kembali untuk masuk.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mt-6">
                            <!-- Form Logout Asli -->
                            <form action="{{ route('logout') }}" method="POST" class="w-1/2">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-red-700 transition shadow-sm">
                                    Ya, Keluar
                                </button>
                            </form>
                            <button @click="showLogoutModal = false" type="button" class="w-1/2 px-4 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content (Tempat halaman spesifik di-render) -->
        <main class="flex-1 flex flex-col h-screen">
            
            <!-- Header Atas -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center">
                <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    @yield('header_title', 'Dashboard')
                </h2>
            </header>

            <!-- Isi Halaman Dinamis -->
            <div class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </div>
            
        </main>
    </div>

</body>
</html>