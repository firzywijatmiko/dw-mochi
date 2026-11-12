<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        // DEFAULT: Tanggal 1 bulan ini sampai tanggal terakhir bulan ini
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. PENGELUARAN (Bahan Baku + Gaji)
        $materialExpenses = DB::table('raw_material_expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('id', 'item_name', 'amount', 'expense_date', 'notes', DB::raw("'material' as type"))
            ->get();

        $payrollExpenses = DB::table('payrolls')
            ->join('employees', 'payrolls.employees_id', '=', 'employees.id')
            ->where('payrolls.status', 'Dibayar')
            ->whereBetween(DB::raw('DATE(payrolls.paid_at)'), [$startDate, $endDate])
            ->select(
                'payrolls.id', 
                DB::raw("CONCAT('Gaji: ', employees.name) as item_name"),
                'payrolls.total_pay as amount', 
                DB::raw("DATE(payrolls.paid_at) as expense_date"),
                DB::raw("CONCAT('Periode ', payrolls.period_start, ' sd ', payrolls.period_end) as notes"),
                DB::raw("'payroll' as type")
            )->get();

        $mergedExpenses = $materialExpenses->merge($payrollExpenses)->sortByDesc('expense_date')->values();
        
        // Pagination untuk Catatan Pengeluaran (25 per halaman)
        $perPageExpenses = 25;
        $currentPageExpenses = LengthAwarePaginator::resolveCurrentPage('expense_page');
        $currentExpensesData = $mergedExpenses->slice(($currentPageExpenses - 1) * $perPageExpenses, $perPageExpenses)->values();
        
        $expenses = new LengthAwarePaginator(
            $currentExpensesData, 
            $mergedExpenses->count(), 
            $perPageExpenses, 
            $currentPageExpenses, 
            ['path' => $request->url(), 'pageName' => 'expense_page']
        );
        $expenses->withQueryString();

        $materialExpense = $materialExpenses->sum('amount');
        $wageExpense     = $payrollExpenses->sum('amount');
        $totalExpense    = $materialExpense + $wageExpense;

        // 2. PEMASUKAN TOTAL
        $totalIncome = (float) DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', 'completed')
            ->whereBetween(DB::raw('DATE(orders.updated_at)'), [$startDate, $endDate])
            ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

        $netProfit = $totalIncome - $totalExpense;

        // 3. RINCIAN HARIAN
        $allDailyReport = [];
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $day = $d->toDateString();
            
            $dayOrders = DB::table('orders')->where('status', 'completed')->whereDate('updated_at', $day)->count();
            
            $dayRevenue = (float) DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.status', 'completed')
                ->whereDate('orders.updated_at', $day)
                ->sum(DB::raw('order_details.quantity * order_details.unit_price'));
            
            $dayMaterial = (float) DB::table('raw_material_expenses')->whereDate('expense_date', $day)->sum('amount');
            $dayWage = (float) DB::table('attendances')->whereDate('date', $day)->sum('daily_wage');

            if($dayOrders > 0 || $dayMaterial > 0 || $dayWage > 0 || $dayRevenue > 0) {
                $allDailyReport[] = [
                    'date' => $day,
                    'orders' => $dayOrders,
                    'revenue' => $dayRevenue,
                    'material' => $dayMaterial,
                    'wage' => $dayWage,
                    'profit' => $dayRevenue - ($dayMaterial + $dayWage)
                ];
            }
        }

        // Urutkan rincian harian dari tanggal terbaru ke terlama
        $collectionDaily = collect($allDailyReport)->sortByDesc('date')->values();

        // Pagination untuk Rincian Harian (25 per halaman)
        $perPageDaily = 25;
        $currentPageDaily = LengthAwarePaginator::resolveCurrentPage('daily_page');
        $currentDailyData = $collectionDaily->slice(($currentPageDaily - 1) * $perPageDaily, $perPageDaily)->values();

        $dailyReport = new LengthAwarePaginator(
            $currentDailyData,
            $collectionDaily->count(),
            $perPageDaily,
            $currentPageDaily,
            ['path' => $request->url(), 'pageName' => 'daily_page']
        );
        $dailyReport->withQueryString();

        // 4. JIKA TOMBOL UNDUH DIKLIK, CETAK PDF (Tetap mencetak semua data tanpa terpotong pagination)
        if ($request->has('download')) {
            $pdf = Pdf::loadView('owner.financial.pdf', compact(
                'startDate', 'endDate', 'totalIncome', 'materialExpense', 
                'wageExpense', 'totalExpense', 'netProfit', 'mergedExpenses', 'allDailyReport'
            ))->setPaper('A4', 'portrait');

            return $pdf->download('Laporan_Keuangan_'.$startDate.'_sd_'.$endDate.'.pdf');
        }

        // 5. DATA GRAFIK MINGGUAN
        $chartData = [];
        $firstWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(4);
        
        for ($i = 0; $i < 5; $i++) {
            $s = $firstWeek->copy()->addWeeks($i)->startOfDay();
            $e = $s->copy()->addDays(6)->endOfDay();
            
            $inc = (float) DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.updated_at', [$s, $e])
                ->sum(DB::raw('order_details.quantity * order_details.unit_price'));
                
            $exp = (float) DB::table('raw_material_expenses')->whereBetween('expense_date', [$s->toDateString(), $e->toDateString()])->sum('amount') 
                 + (float) DB::table('attendances')->whereBetween('date', [$s->toDateString(), $e->toDateString()])->sum('daily_wage');
                 
            if ($s->format('M') !== $e->format('M')) {
                $labelRange = $s->format('j M') . ' - ' . $e->format('j M');
            } else {
                $labelRange = $s->format('j') . ' - ' . $e->format('j M');
            }

            $chartData[] = [
                'label'   => $labelRange, 
                'income'  => $inc, 
                'expense' => $exp
            ];
        }
        
        $highest = max(array_merge([0], array_column($chartData, 'income'), array_column($chartData, 'expense')));
        $chartMax = ($highest <= 0) ? 100000 : ceil($highest / pow(10, floor(log10($highest)))) * pow(10, floor(log10($highest)));

        return view('owner.financial.index', compact(
            'expenses', 'totalIncome', 'totalExpense', 'netProfit', 
            'chartData', 'chartMax', 'startDate', 'endDate', 'dailyReport'
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

        return redirect('/owner/keuangan')->with('success', 'Catatan pengeluaran berhasil ditambahkan!');
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

        return redirect('/owner/keuangan')->with('success', 'Catatan pengeluaran berhasil diubah!');
    }

    public function destroy($id)
    {
        DB::table('raw_material_expenses')->where('id', $id)->delete();
        return redirect('/owner/keuangan')->with('success', 'Catatan pengeluaran berhasil dihapus!');
    }
}