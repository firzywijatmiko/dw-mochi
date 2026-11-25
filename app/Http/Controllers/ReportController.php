<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $perPage   = (int) $request->input('per_page', 10);
        $chartMode = $request->input('chart_mode', 'weekly'); // default: weekly (daily | weekly | monthly)

        // Pastikan urutan tanggal valid jika user terbalik memilih
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // 1. PENGELUARAN (Bahan Baku + Gaji yang Sudah Dibayar)
        $materialExpenses = DB::table('raw_material_expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('id', 'item_name', 'amount', 'expense_date', 'notes', DB::raw("'material' as type"))
            ->get();

        $payrollExpenses = DB::table('payrolls')
            ->join('employees', 'payrolls.employees_id', '=', 'employees.id')
            ->where('payrolls.status', 'Dibayar')
            ->whereNotNull('payrolls.paid_at')
            ->whereBetween(DB::raw('DATE(payrolls.paid_at)'), [$startDate, $endDate])
            ->select(
                'payrolls.id',
                DB::raw("CONCAT('Gaji: ', employees.name) as item_name"),
                'payrolls.total_pay as amount',
                DB::raw("DATE(payrolls.paid_at) as expense_date"),
                DB::raw("CONCAT('Periode ', DATE_FORMAT(payrolls.period_start, '%d/%m/%Y'), ' - ', DATE_FORMAT(payrolls.period_end, '%d/%m/%Y')) as notes"),
                DB::raw("'payroll' as type")
            )->get();

        $mergedExpenses  = $materialExpenses->merge($payrollExpenses)->sortByDesc('expense_date')->values();
        $materialExpense = (float) $materialExpenses->sum('amount');
        $wageExpense     = (float) $payrollExpenses->sum('amount');
        $totalExpense    = $materialExpense + $wageExpense;

        // 2. PEMASUKAN TOTAL
        $totalIncome = (float) DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', 'completed')
            ->whereBetween(DB::raw('DATE(orders.updated_at)'), [$startDate, $endDate])
            ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

        $netProfit = $totalIncome - $totalExpense;

        // 3. RINCIAN ARUS KAS HARIAN
        $allDailyReport = [];
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $day = $d->toDateString();

            $dayOrders = DB::table('orders')
                ->where('status', 'completed')
                ->whereDate('updated_at', $day)
                ->count();

            $dayRevenue = (float) DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.status', 'completed')
                ->whereDate('orders.updated_at', $day)
                ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

            $dayMaterial = (float) DB::table('raw_material_expenses')
                ->whereDate('expense_date', $day)
                ->sum('amount');

            $dayWage = (float) DB::table('payrolls')
                ->where('status', 'Dibayar')
                ->whereDate('paid_at', $day)
                ->sum('total_pay');

            if ($dayOrders > 0 || $dayMaterial > 0 || $dayWage > 0 || $dayRevenue > 0) {
                $allDailyReport[] = [
                    'date'     => $day,
                    'orders'   => $dayOrders,
                    'revenue'  => $dayRevenue,
                    'material' => $dayMaterial,
                    'wage'     => $dayWage,
                    'profit'   => $dayRevenue - ($dayMaterial + $dayWage)
                ];
            }
        }

        $collectionDaily = collect($allDailyReport)->sortByDesc('date')->values();

        // Pagination Rincian Harian
        $currentPageDaily = LengthAwarePaginator::resolveCurrentPage('page');
        $currentDailyData = $collectionDaily->slice(($currentPageDaily - 1) * $perPage, $perPage)->values();

        $dailyReport = new LengthAwarePaginator(
            $currentDailyData,
            $collectionDaily->count(),
            $perPage,
            $currentPageDaily,
            ['path' => $request->url(), 'pageName' => 'page']
        );
        $dailyReport->withQueryString();

        // 4. CETAK PDF
        if ($request->has('download')) {
            $pdf = Pdf::loadView('owner.reports.pdf', compact(
                'startDate', 'endDate', 'totalIncome', 'materialExpense',
                'wageExpense', 'totalExpense', 'netProfit', 'mergedExpenses', 'allDailyReport'
            ))->setPaper('A4', 'portrait');

            return $pdf->download('Laporan_Keuangan_' . $startDate . '_sd_' . $endDate . '.pdf');
        }

        // 5. DATA GRAFIK SESUAI PILIHAN OWNER (daily / weekly / monthly)
        $chartData = [];

        if ($chartMode === 'daily') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dayStr = $cursor->toDateString();

                $inc = (float) DB::table('order_details')
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    ->where('orders.status', 'completed')
                    ->whereDate('orders.updated_at', $dayStr)
                    ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

                $matExp = (float) DB::table('raw_material_expenses')
                    ->whereDate('expense_date', $dayStr)
                    ->sum('amount');

                $payExp = (float) DB::table('payrolls')
                    ->where('status', 'Dibayar')
                    ->whereDate('paid_at', $dayStr)
                    ->sum('total_pay');

                $chartData[] = [
                    'label'   => $cursor->format('j M'),
                    'income'  => $inc,
                    'expense' => $matExp + $payExp
                ];

                $cursor->addDay();
            }
        } elseif ($chartMode === 'monthly') {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $qStart = $cursor->lt($start) ? $start->copy() : $cursor->copy()->startOfMonth();
                $qEnd   = $cursor->copy()->endOfMonth()->gt($end) ? $end->copy() : $cursor->copy()->endOfMonth();

                $inc = (float) DB::table('order_details')
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    ->where('orders.status', 'completed')
                    ->whereBetween(DB::raw('DATE(orders.updated_at)'), [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

                $matExp = (float) DB::table('raw_material_expenses')
                    ->whereBetween('expense_date', [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum('amount');

                $payExp = (float) DB::table('payrolls')
                    ->where('status', 'Dibayar')
                    ->whereBetween(DB::raw('DATE(paid_at)'), [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum('total_pay');

                $chartData[] = [
                    'label'   => $cursor->translatedFormat('M Y'),
                    'income'  => $inc,
                    'expense' => $matExp + $payExp
                ];

                $cursor->addMonth();
            }
        } else {
            // Default: 'weekly' (Blok 7 hari dari $startDate)
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $qStart = $cursor->copy();
                $qEnd   = $cursor->copy()->addDays(6);
                if ($qEnd->gt($end)) {
                    $qEnd = $end->copy();
                }

                $inc = (float) DB::table('order_details')
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    ->where('orders.status', 'completed')
                    ->whereBetween(DB::raw('DATE(orders.updated_at)'), [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

                $matExp = (float) DB::table('raw_material_expenses')
                    ->whereBetween('expense_date', [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum('amount');

                $payExp = (float) DB::table('payrolls')
                    ->where('status', 'Dibayar')
                    ->whereBetween(DB::raw('DATE(paid_at)'), [$qStart->toDateString(), $qEnd->toDateString()])
                    ->sum('total_pay');

                $labelRange = ($qStart->format('M') !== $qEnd->format('M'))
                    ? $qStart->format('j M') . ' - ' . $qEnd->format('j M')
                    : ($qStart->eq($qEnd) ? $qStart->format('j M') : $qStart->format('j') . ' - ' . $qEnd->format('j M'));

                $chartData[] = [
                    'label'   => $labelRange,
                    'income'  => $inc,
                    'expense' => $matExp + $payExp
                ];

                $cursor->addDays(7);
            }
        }

        $highest  = max(array_merge([0], array_column($chartData, 'income'), array_column($chartData, 'expense')));
        $chartMax = ($highest <= 0) ? 100000 : ceil($highest / pow(10, floor(log10($highest)))) * pow(10, floor(log10($highest)));

        return view('owner.reports.index', compact(
            'totalIncome', 'materialExpense', 'wageExpense', 'totalExpense',
            'netProfit', 'chartData', 'chartMax', 'startDate', 'endDate', 'dailyReport',
            'mergedExpenses', 'allDailyReport', 'perPage', 'chartMode'
        ));
    }
}