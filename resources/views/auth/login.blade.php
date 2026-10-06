<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DW Mochi</title>
    <!-- Menggunakan Tailwind CSS untuk styling sesuai prototype -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f7f7f7] flex items-center justify-center min-h-screen">

    <!-- Card Container -->
    <div class="bg-white p-8 rounded-xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] w-full max-w-[340px]">
        
        <!-- Logo & Header -->
        <div class="flex flex-col items-center mb-6">
            <div class="w-12 h-12 bg-[#0f2137] rounded-xl flex items-center justify-center mb-4">
                <!-- Icon Cookie/Mochi SVG -->
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 11-10 10 1 1 0 0110-10zm0 0v2m0 16v2m8-10h2M2 12h2m13.95-7.05l-1.41 1.41M5.46 18.54l-1.41 1.41M18.54 18.54l-1.41-1.41M5.46 5.46l1.41 1.41"></path>
                </svg>
            </div>
            <h1 class="text-[#0f2137] text-lg font-bold">DW Mochi</h1>
            <p class="text-[#c28455] text-[10px] mt-0.5">Sistem Informasi Manajemen Operasional</p>
        </div>

        <!-- Menampilkan Pesan Error / Success dari Controller & Middleware -->
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded mb-4 text-xs">
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 px-3 py-2 rounded mb-4 text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded mb-4 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Input Username (BAGIAN YANG DIPERBAIKI) -->
            <div>
                <label for="username" class="block text-[11px] font-semibold text-[#0f2137] mb-1.5">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-[#0f2137] focus:ring-1 focus:ring-[#0f2137] transition"
                    placeholder="Masukkan username">
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-[11px] font-semibold text-[#0f2137] mb-1.5">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:border-[#0f2137] focus:ring-1 focus:ring-[#0f2137] transition"
                    placeholder="Masukkan password">
            </div>

            <!-- Tombol Masuk -->
            <div class="pt-2">
                <button type="submit" 
                    class="w-full bg-[#0f2137] text-white font-medium text-xs py-2.5 rounded-lg hover:bg-[#183252] transition duration-200 shadow-sm">
                    Masuk
                </button>
            </div>
        </form>

        <!-- Footer Demo -->
        <div class="mt-5 text-center">
            <p class="text-[9px] text-gray-400">Demo: <span class="text-[#c28455]">admin / mochi123</span></p>
        </div>
        
    </div>

</body>
</html>