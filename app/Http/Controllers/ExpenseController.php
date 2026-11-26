<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $search    = $request->input('search');
        $type      = $request->input('type'); // 'material', 'payroll', atau null (semua)

        // 1. Pengeluaran Bahan Baku / Operasional
        $materialQuery = DB::table('raw_material_expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('item_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->select('id', 'item_name', 'amount', 'expense_date', 'notes', DB::raw("'material' as type"));

        $materialExpenses = ($type === 'payroll') ? collect() : $materialQuery->get();

        // 2. Pengeluaran Gaji Karyawan (Otomatis dari Payroll yang sudah Dibayar)
        $payrollQuery = DB::table('payrolls')
            ->join('employees', 'payrolls.employees_id', '=', 'employees.id')
            ->where('payrolls.status', 'Dibayar')
            ->whereBetween(DB::raw('DATE(payrolls.paid_at)'), [$startDate, $endDate])
            ->when($search, function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%");
            })
            ->select(
                'payrolls.id',
                DB::raw("CONCAT('Gaji: ', employees.name) as item_name"),
                'payrolls.total_pay as amount',
                DB::raw("DATE(payrolls.paid_at) as expense_date"),
                DB::raw("CONCAT('Periode ', payrolls.period_start, ' s/d ', payrolls.period_end) as notes"),
                DB::raw("'payroll' as type")
            );

        $payrollExpenses = ($type === 'material') ? collect() : $payrollQuery->get();

        // Hitung ringkasan total periode (tanpa terpengaruh filter jenis/search agar kartu ringkasan akurat)
        $totalMaterial = (float) DB::table('raw_material_expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $totalPayroll = (float) DB::table('payrolls')
            ->where('status', 'Dibayar')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$startDate, $endDate])
            ->sum('total_pay');

        $totalExpense = $totalMaterial + $totalPayroll;

        // Gabung dan urutkan dari tanggal terbaru
        $mergedExpenses = $materialExpenses->merge($payrollExpenses)->sortByDesc('expense_date')->values();

        // Pagination: jumlah per halaman mengikuti ?per_page (hanya 5, 10, 25, 50)
        $perPage = in_array((int) $request->input('per_page'), [5, 10, 25, 50])
            ? (int) $request->input('per_page')
            : 10;

        $total       = $mergedExpenses->count();
        $lastPage    = max((int) ceil($total / $perPage), 1);
        $currentPage = min(max(LengthAwarePaginator::resolveCurrentPage(), 1), $lastPage);
        $currentData = $mergedExpenses->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $expenses = new LengthAwarePaginator(
            $currentData,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        return view('owner.expenses.index', compact(
            'expenses',
            'totalMaterial',
            'totalPayroll',
            'totalExpense',
            'startDate',
            'endDate',
            'search',
            'type',
            'perPage'
        ));
    }

    public function store(Request $request)
    {
        $cleanAmount = str_replace('.', '', $request->input('amount'));
        $request->merge(['amount' => $cleanAmount]);

        $validated = $request->validate([
            'item_name'    => 'required|string|max:255',
            'amount'       => 'required|integer|min:1|max:100000000',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ], [
            'amount.required' => 'Nominal wajib diisi.',
            'amount.integer'  => 'Nominal harus berupa bilangan bulat.',
            'amount.min'      => 'Nominal minimal Rp 1.',
            'amount.max'      => 'Nominal maksimal Rp 100.000.000.',
        ]);

        DB::table('raw_material_expenses')->insert([
            'created_by'   => Auth::id(),
            'item_name'    => $validated['item_name'],
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes'        => $request->notes ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('owner.expenses.index')->with('success', 'Catatan pengeluaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $cleanAmount = str_replace('.', '', $request->input('amount'));
        $request->merge(['amount' => $cleanAmount]);

        $validated = $request->validate([
            'item_name'    => 'required|string|max:255',
            'amount'       => 'required|integer|min:1|max:100000000',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ], [
            'amount.required' => 'Nominal wajib diisi.',
            'amount.integer'  => 'Nominal harus berupa bilangan bulat.',
            'amount.min'      => 'Nominal minimal Rp 1.',
            'amount.max'      => 'Nominal maksimal Rp 100.000.000.',
        ]);

        DB::table('raw_material_expenses')->where('id', $id)->update([
            'item_name'    => $validated['item_name'],
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes'        => $request->notes ?? null,
            'updated_at'   => now(),
        ]);

        return redirect()->route('owner.expenses.index')->with('success', 'Catatan pengeluaran berhasil diubah.');
    }

    public function destroy($id)
    {
        DB::table('raw_material_expenses')->where('id', $id)->delete();
        return redirect()->route('owner.expenses.index')->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }
}