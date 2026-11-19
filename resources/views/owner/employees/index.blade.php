<!-- resources/views/owner/employees/index.blade.php -->
@extends('layouts.app')

@section('header_title', 'Manajemen Karyawan')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div x-data="employeeManager()" class="space-y-4">
    
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="flex justify-end">
        <button @click="openCreateModal()" class="inline-flex items-center gap-1.5 rounded-md bg-[#0f2137] px-3 py-2 text-[10px] font-semibold text-white hover:bg-[#183252]">
            <span>＋</span> Tambah Karyawan
        </button>
    </div>

    <!-- Card Gabungan Tabel Data Karyawan & Pagination -->
    <div class="overflow-hidden rounded-lg border border-[#e7e0d8] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[750px] w-full text-left text-[10px]">
                <thead class="border-b border-[#e7e0d8] bg-[#faf9f7] uppercase text-[9px] tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Username Login</th>
                        <th class="px-4 py-3">No. HP</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Gaji Pokok</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1eeeb] text-gray-600">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-[#fcfbf9] transition">
                        <td class="px-4 py-3 font-semibold text-[#0f2137]">{{ $emp->name }}</td>
                        <td class="px-4 py-3">{{ $emp->user->username ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $emp->user->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-[9px] font-semibold {{ $emp->status === 'Aktif' ? 'bg-[#dff5e9] text-[#348b66]' : 'bg-[#fee8e7] text-[#bd4d48]' }}">
                                {{ $emp->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-semibold">Rp {{ number_format($emp->base_daily_wage, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button @click="openEditModal({{ $emp }})" class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-50">
                                    <span>✎</span> Ubah
                                </button>
                                <button @click="openDeleteModal({{ $emp->id }}, '{{ addslashes($emp->name) }}')" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-[10px] font-semibold text-red-500 hover:bg-red-100">
                                    <span>🗑</span> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada data karyawan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tabel: Pagination Terintegrasi --}}
        @if(method_exists($employees, 'total'))
            @php $currentPerPage = $perPage ?? request('per_page', 10); @endphp
            <div class="flex flex-col gap-3 border-t border-[#e7e0d8] bg-[#faf9f7] px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-600">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('owner.employees.index') }}" method="GET" class="flex items-center gap-1.5">
                        <select name="per_page" onchange="this.form.submit()" class="rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 focus:border-[#0f2137] focus:outline-none">
                            @foreach([5, 10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="text-gray-400">per halaman</span>
                    </form>

                    <span class="hidden sm:inline text-gray-300">|</span>

                    <span class="text-gray-500">
                        Menampilkan <strong class="text-gray-700">{{ $employees->firstItem() ?? 0 }} - {{ $employees->lastItem() ?? 0 }}</strong> dari <strong class="text-gray-700">{{ $employees->total() }}</strong> data
                    </span>
                </div>

                @if($employees->hasPages())
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            @if($employees->onFirstPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $employees->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $current = $employees->currentPage();
                                $last    = $employees->lastPage();
                                $start   = max(1, $current - 2);
                                $end     = min($last, $current + 2);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $employees->url(1) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">1</a>
                                @if($start > 2) <span class="px-1 text-gray-400">...</span> @endif
                            @endif

                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-[#0f2137] px-2 text-xs font-semibold text-white shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $employees->url($i) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60 transition">{{ $i }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1) <span class="px-1 text-gray-400">...</span> @endif
                                <a href="{{ $employees->url($last) }}" class="flex h-7 min-w-[28px] items-center justify-center rounded-md px-2 text-xs text-gray-600 hover:bg-gray-200/60">{{ $last }}</a>
                            @endif

                            @if($employees->hasMorePages())
                                <a href="{{ $employees->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 hover:bg-gray-200/60 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-md text-gray-300 cursor-not-allowed">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>

                        <form action="{{ route('owner.employees.index') }}" method="GET" class="flex items-center gap-1.5">
                            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                            <span class="text-gray-400">Ke hal:</span>
                            <input type="number" name="page" min="1" max="{{ $employees->lastPage() }}" placeholder="{{ $employees->currentPage() }}" class="w-14 rounded-md border border-gray-200 bg-white px-2 py-1 text-center text-xs text-gray-700 focus:border-[#0f2137] focus:outline-none">
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- MODAL FORM (TAMBAH / UBAH) -->
    <div x-cloak x-show="isFormModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="closeFormModal()" class="w-full max-w-xl rounded-xl bg-white shadow-xl" x-transition>
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-bold text-[#0f2137]" x-text="isEdit ? 'Ubah Data Karyawan' : 'Tambah Karyawan Baru'"></h3>
                <button type="button" @click="closeFormModal()" class="text-gray-400">✕</button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4 p-5 max-h-[80vh] overflow-y-auto">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <h4 class="text-[10px] font-bold text-gray-400 uppercase border-b border-gray-100 pb-1 mb-2">Informasi Akun</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="text-[10px] font-semibold text-gray-600">NAMA KARYAWAN *
                        <input type="text" name="name" x-model="formData.name" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="Nama lengkap">
                    </label>
                    <label class="text-[10px] font-semibold text-gray-600">NO. HP *
                        <input type="text" name="phone" x-model="formData.phone" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="08xxxxxxxxxx">
                    </label>
                    <label class="text-[10px] font-semibold text-gray-600">USERNAME LOGIN *
                        <input type="text" name="username" x-model="formData.username" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" placeholder="Contoh: karyawan01">
                    </label>
                    <label class="text-[10px] font-semibold text-gray-600">PASSWORD <span x-show="!isEdit">*</span>
                        <input type="password" name="password" :required="!isEdit" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs" :placeholder="isEdit ? 'Kosongkan jika tak ganti' : 'Minimal 6 karakter'">
                    </label>
                </div>

                <h4 class="text-[10px] font-bold text-gray-400 uppercase border-b border-gray-100 pb-1 mt-4 mb-2">Detail Gaji & Status</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="text-[10px] font-semibold text-gray-600 sm:col-span-1">STATUS PEKERJA
                        <select name="status" x-model="formData.status" required class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-xs bg-white">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </label>
                    
                    <div class="sm:col-span-2 grid grid-cols-3 gap-2">
                        <label class="text-[9px] font-semibold text-gray-600 uppercase">GAJI HARIAN (RP)
                            <input type="text" x-model="displayWage" @input="formatWage($event, 'base_daily_wage')" required class="mt-1 w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold" placeholder="0">
                            <input type="hidden" name="base_daily_wage" x-model="formData.base_daily_wage">
                        </label>
                        <label class="text-[9px] font-semibold text-gray-600 uppercase">LBR 1X / JAM
                            <input type="text" x-model="displayOt1" @input="formatWage($event, 'overtime_rate_1x')" class="mt-1 w-full rounded-md border border-blue-200 bg-blue-50/50 px-3 py-2 text-xs" placeholder="0">
                            <input type="hidden" name="overtime_rate_1x" x-model="formData.overtime_rate_1x">
                        </label>
                        <label class="text-[9px] font-semibold text-gray-600 uppercase">LBR 2X / JAM
                            <input type="text" x-model="displayOt2" @input="formatWage($event, 'overtime_rate_2x')" class="mt-1 w-full rounded-md border border-orange-200 bg-orange-50/50 px-3 py-2 text-xs" placeholder="0">
                            <input type="hidden" name="overtime_rate_2x" x-model="formData.overtime_rate_2x">
                        </label>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-gray-100 pt-4 mt-2">
                    <button type="submit" class="rounded-md bg-[#0f2137] px-4 py-2 text-xs font-semibold text-white" x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Karyawan'"></button>
                    <button type="button" @click="closeFormModal()" class="rounded-md border border-gray-200 px-4 py-2 text-xs text-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div x-cloak x-show="isDeleteModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f2137]/40 p-4 backdrop-blur-sm">
        <div @click.away="isDeleteModalOpen = false" class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl" x-transition>
            <h3 class="text-sm font-bold text-[#0f2137]">Hapus Karyawan?</h3>
            <p class="mt-3 text-xs text-gray-500">Yakin ingin menghapus <strong x-text="deleteEmployeeName"></strong>?</p>
            
            <form :action="deleteActionUrl" method="POST" class="mt-5 flex gap-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex-1 rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white">Ya, Hapus</button>
                <button type="button" @click="isDeleteModalOpen = false" class="flex-1 rounded-md border border-gray-200 px-4 py-2 text-xs">Batal</button>
            </form>
        </div>
    </div>
</div>

<script>
    function employeeManager() {
        return {
            isFormModalOpen: false, isDeleteModalOpen: false, isEdit: false,
            formAction: '', deleteActionUrl: '', deleteEmployeeName: '',
            formData: { name: '', username: '', phone: '', status: 'Aktif', base_daily_wage: '', overtime_rate_1x: '', overtime_rate_2x: '' },
            displayWage: '', displayOt1: '', displayOt2: '',

            formatRupiah(value) {
                if (!value && value !== 0) return '';
                let numberString = value.toString().replace(/[^,\d]/g, '');
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            },

            formatWage(event, field) {
                let rawVal = event.target.value.replace(/[^0-9]/g, '');
                this.formData[field] = rawVal;
                let formatted = this.formatRupiah(rawVal);
                if (field === 'base_daily_wage') this.displayWage = formatted;
                if (field === 'overtime_rate_1x') this.displayOt1 = formatted;
                if (field === 'overtime_rate_2x') this.displayOt2 = formatted;
            },

            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('owner.employees.store') }}";
                this.formData = { name: '', username: '', phone: '', status: 'Aktif', base_daily_wage: '', overtime_rate_1x: '', overtime_rate_2x: '' };
                this.displayWage = ''; this.displayOt1 = ''; this.displayOt2 = '';
                this.isFormModalOpen = true;
            },

            openEditModal(employee) {
                this.isEdit = true;
                let baseUrl = "{{ route('owner.employees.update', ':id') }}";
                this.formAction = baseUrl.replace(':id', employee.id);
                this.formData = { 
                    name: employee.name, 
                    username: employee.user ? employee.user.username : '',
                    phone: employee.user ? employee.user.phone : '', 
                    status: employee.status, 
                    base_daily_wage: employee.base_daily_wage || '',
                    overtime_rate_1x: employee.overtime_rate_1x || '',
                    overtime_rate_2x: employee.overtime_rate_2x || ''
                };
                this.displayWage = this.formatRupiah(employee.base_daily_wage || '');
                this.displayOt1 = this.formatRupiah(employee.overtime_rate_1x || '');
                this.displayOt2 = this.formatRupiah(employee.overtime_rate_2x || '');
                this.isFormModalOpen = true;
            },

            closeFormModal() { this.isFormModalOpen = false; },

            openDeleteModal(id, name) {
                let baseUrl = "{{ route('owner.employees.destroy', ':id') }}";
                this.deleteActionUrl = baseUrl.replace(':id', id);
                this.deleteEmployeeName = name;
                this.isDeleteModalOpen = true;
            }
        }
    }
</script>
@endsection