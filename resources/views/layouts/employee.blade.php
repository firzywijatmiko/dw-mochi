<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DW Mochi - Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<!-- Tambahkan x-data untuk kontrol modal logout di body -->
<body class="bg-[#f0f2f5] min-h-screen text-gray-800 antialiased overflow-x-hidden" x-data="{ showLogoutModal: false }">
    
    <!-- Hapus max-w-4xl dan mx-auto agar kembali full screen -->
    <div class="w-full min-h-screen flex flex-col relative">
        @yield('content')
    </div>

    <!-- MODAL KONFIRMASI LOGOUT -->
    <template x-teleport="body">
        <div x-cloak x-show="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
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

</body>
</html>