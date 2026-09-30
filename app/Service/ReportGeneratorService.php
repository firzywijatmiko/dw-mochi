<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RawMaterialExpense;
use App\Models\Attendance;
use Carbon\Carbon;

class ReportGeneratorService
{
    public function generateWeeklyReport($startDate, $endDate)
    {
        // 1. Ambil Data Pemasukan dari Pesanan yang selesai
        $orders = Order::whereBetween('pickup_at', [$startDate, $endDate])
                       ->where('status', 'completed') // Asumsi pemasukan dihitung dari pesanan selesai
                       ->get();
        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_price');

        // 2. Ambil Data Pengeluaran Operasional / Bahan Baku[cite: 22]
        $materialExpenses = RawMaterialExpense::whereBetween('expense_date', [$startDate, $endDate])->get();
        $totalMaterialExpense = $materialExpenses->sum('amount');

        // 3. Ambil Data Pengeluaran Gaji Karyawan[cite: 22]
        $attendances = Attendance::whereBetween('date', [$startDate, $endDate])->get();
        $totalWageExpense = $attendances->sum('daily_wage');

        // 4. Kalkulasi Keuntungan Bersih[cite: 22]
        $totalExpense = $totalMaterialExpense + $totalWageExpense;
        $netProfit = $totalRevenue - $totalExpense;

        return [
            'week_start' => $startDate,
            'week_end' => $endDate,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_material_expense' => $totalMaterialExpense,
            'total_wage_expense' => $totalWageExpense,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
            'generated_at' => Carbon::now()
        ];
    }
}