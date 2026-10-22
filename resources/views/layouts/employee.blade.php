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
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800 antialiased overflow-x-hidden">
    <!-- Kontainer dengan batas maksimal lebar untuk tablet/desktop agar tidak terlalu melebar, namun full di HP -->
    <div class="w-full min-h-screen bg-gray-50 mx-auto max-w-4xl flex flex-col relative">
        @yield('content')
    </div>
</body>
</html>