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
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#f0f2f5] text-gray-800" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Overlay Hitam untuk Layar HP saat Sidebar Aktif -->
        <div x-cloak
             x-show="sidebarOpen"
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-30 w-64 transform bg-[#0f2137] text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col justify-between h-full">
            <div>
                <!-- Bagian Logo DW Mochi -->
                <div class="flex items-center justify-between px-6 py-6 border-b border-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 11-10 10 1 1 0 0110-10zm0 0v2m0 16v2m8-10h2M2 12h2m13.95-7.05l-1.41 1.41M5.46 18.54l-1.41 1.41M18.54 18.54l-1.41-1.41M5.46 5.46l1.41 1.41"></path></svg>
                        </div>
                        <div>
                            <h1 class="font-bold text-sm">DW Mochi</h1>
                            <p class="text-[10px] text-gray-400">Sistem Manajemen</p>
                        </div>
                    </div>
                    <button type="button" @click.stop="sidebarOpen = false" class="text-gray-400 hover:text-white lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Menu Navigasi -->
                <nav class="mt-6 px-4 space-y-1 h-[calc(100vh-180px)] overflow-y-auto scrollbar-hide lg:h-auto lg:overflow-visible">
                    
                    @php
                        $isDashboard   = request()->routeIs('owner.dashboard');
                        $isOrders      = request()->routeIs('owner.orders.*');
                        $isProducts    = request()->routeIs('owner.product_variants.*');
                        $isEmployees   = request()->routeIs('owner.employees.*');
                        $isAttendances = request()->routeIs('owner.attendances.*');
                        $isPayrolls    = request()->routeIs('owner.payrolls.*');
                        $isExpenses    = request()->routeIs('owner.expenses.*');
                        $isReports     = request()->routeIs('owner.reports.*');
                    @endphp

                    <a href="{{ route('owner.dashboard') }}"
                       @if($isDashboard) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isDashboard ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('owner.orders.index') }}"
                       @if($isOrders) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isOrders ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Pesanan
                    </a>

                    <a href="{{ route('owner.product_variants.index') }}"
                       @if($isProducts) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isProducts ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Produk
                    </a>

                    <a href="{{ route('owner.employees.index') }}"
                       @if($isEmployees) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isEmployees ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Karyawan
                    </a>
                    
                    <a href="{{ route('owner.attendances.recap') }}"
                       @if($isAttendances) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isAttendances ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Presensi
                    </a>

                    <a href="{{ route('owner.payrolls.index') }}"
                       @if($isPayrolls) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isPayrolls ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Penggajian
                    </a>

                    {{-- MENU TERPISAH 1: PENGELUARAN --}}
                    <a href="{{ route('owner.expenses.index') }}"
                       @if($isExpenses) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isExpenses ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
                        Pengeluaran
                    </a>

                    {{-- MENU TERPISAH 2: LAPORAN --}}
                    <a href="{{ route('owner.reports.index') }}"
                       @if($isReports) @click.prevent="sidebarOpen = false" @else @click="sidebarOpen = false" @endif
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition {{ $isReports ? 'bg-[#e8d5c4] text-[#0f2137] font-semibold' : 'text-gray-300 hover:bg-white/10' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Laporan
                    </a>
                </nav>
            </div>

            <!-- Bagian Bawah: Tombol Logout -->
            <div class="p-4 bg-[#0f2137]" x-data="{ showLogoutModal: false }">
                <button @click="showLogoutModal = true" type="button" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/15 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>

                <template x-teleport="body">
                    <div x-show="showLogoutModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
                        <div @click.away="showLogoutModal = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden p-6 text-gray-800" x-transition>
                            <div class="flex items-start sm:items-center gap-4 mb-4">
                                <div class="p-3 bg-red-50 text-red-500 rounded-full shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Keluar Sistem?</h3>
                                    <p class="text-xs text-gray-500 mt-1">Anda harus login kembali untuk masuk.</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row items-center gap-3 mt-6">
                                <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-1/2">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2.5 sm:py-2 rounded-lg text-xs font-medium hover:bg-red-700 transition shadow-sm">
                                        Ya, Keluar
                                    </button>
                                </form>
                                <button @click="showLogoutModal = false" type="button" class="w-full sm:w-1/2 px-4 py-2.5 sm:py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen w-full overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center">
                <button type="button" @click.stop="sidebarOpen = true" class="mr-3 p-1 -ml-1 text-gray-600 focus:outline-none lg:hidden">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <h2 class="text-sm font-semibold text-gray-700 truncate">
                    @yield('header_title', 'Dashboard')
                </h2>
            </header>

            <div class="flex-1 overflow-y-auto p-4 sm:p-6 w-full">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>