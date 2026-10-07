<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\RawMaterialExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportGeneratorService
{
    /**
     * Hitung laporan operasional untuk rentang tanggal, simpan ke tabel
     * weekly_reports (diperbarui jika periode yang sama sudah pernah dibuat),
     * lalu kembalikan datanya untuk ditampilkan.
     */
    public function generateWeeklyReport($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        // Pesanan selesai & pendapatan (konsisten dengan DashboardController)
        $totalOrders = Order::where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $totalRevenue = $this->revenueBetween($start, $end);

        // Pengeluaran bahan baku
        $materialExpense = (float) RawMaterialExpense::whereBetween('expense_date', [
            $start->toDateString(), $end->toDateString(),
        ])->sum('amount');

        // Pengeluaran gaji (jumlah gaji harian dari presensi)
        $wageExpense = (float) Attendance::whereBetween('date', [
            $start->toDateString(), $end->toDateString(),
        ])->sum('daily_wage');

        $totalExpense = $materialExpense + $wageExpense;
        $netProfit    = $totalRevenue - $totalExpense;

        $summary = [
            'week_start'             => $start->toDateString(),
            'week_end'               => $end->toDateString(),
            'total_orders'           => $totalOrders,
            'total_revenue'          => $totalRevenue,
            'total_expense'          => $totalExpense,
            'total_material_expense' => $materialExpense,
            'total_wage_expense'     => $wageExpense,
            'net_profit'             => $netProfit,
            'generated_at'           => Carbon::now(),
        ];

        $this->saveReport($summary);

        $summary['daily'] = $this->dailyBreakdown($start, $end);

        return $summary;
    }

    private function revenueBetween(Carbon $from, Carbon $to): float
    {
        return (float) OrderDetail::whereHas('order', function ($q) use ($from, $to) {
            $q->where('status', 'completed')->whereBetween('updated_at', [$from, $to]);
        })->sum(DB::raw('quantity * unit_price'));
    }

    private function dailyBreakdown(Carbon $start, Carbon $end): array
    {
        $rows = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $day = $d->toDateString();

            $material = (float) RawMaterialExpense::whereDate('expense_date', $day)->sum('amount');
            $wage     = (float) Attendance::whereDate('date', $day)->sum('daily_wage');
            $revenue  = $this->revenueBetween($d->copy()->startOfDay(), $d->copy()->endOfDay());

            $rows[] = [
                'date'             => $day,
                'orders'           => Order::where('status', 'completed')->whereDate('updated_at', $day)->count(),
                'revenue'          => $revenue,
                'material_expense' => $material,
                'wage_expense'     => $wage,
                'profit'           => $revenue - $material - $wage,
            ];
        }

        return $rows;
    }

    private function saveReport(array $s): void
    {
        $data = [
            'total_orders'           => $s['total_orders'],
            'total_revenue'          => $s['total_revenue'],
            'total_expense'          => $s['total_expense'],
            'total_material_expense' => $s['total_material_expense'],
            'total_wage_expense'     => $s['total_wage_expense'],
            'net_profit'             => $s['net_profit'],
            'generated_at'           => $s['generated_at'],
            'updated_at'             => Carbon::now(),
        ];

        $query = DB::table('weekly_reports')
            ->where('week_start', $s['week_start'])
            ->where('week_end', $s['week_end']);

        if ($query->exists()) {
            $query->update($data);
        } else {
            DB::table('weekly_reports')->insert($data + [
                'week_start' => $s['week_start'],
                'week_end'   => $s['week_end'],
                'created_at' => Carbon::now(),
            ]);
        }
    }
}