<!-- resources/views/owner/partials/payroll_tabs.blade.php -->
<div class="flex gap-2 mb-6">
    <a href="{{ route('owner.attendances.recap') }}"
       class="px-4 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('owner.attendances.*') ? 'bg-[#0f2137] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        Rekap Presensi
    </a>
    <a href="{{ route('owner.payrolls.index') }}"
       class="px-4 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('owner.payrolls.*') ? 'bg-[#0f2137] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        Penggajian & Riwayat
    </a>
</div>