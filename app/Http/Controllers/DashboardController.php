<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RawMaterialExpense;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mengambil dan menyusun statistik data pesanan, keuangan, dan presensi[cite: 25]
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Ambil Data Pesanan[cite: 25]
        $activeOrders = Order::whereIn('status', ['pending', 'process'])->count();
        $completedToday = Order::where('status', 'completed')->whereDate('updated_at', $today)->count();

        // 2. Ambil Data Keuangan[cite: 25]
        $todayRevenue = Order::where('status', 'completed')->whereDate('updated_at', $today)->sum('total_price');
        $todayExpense = RawMaterialExpense::whereDate('expense_date', $today)->sum('amount');

        // 3. Ambil Data Presensi[cite: 25]
        $presentEmployees = Attendance::where('date', $today)->whereNotNull('check_in_at')->count();

        // 4. Susun Data Statistik[cite: 25]
        $statistics = [
            'active_orders' => $activeOrders,
            'completed_today' => $completedToday,
            'today_revenue' => $todayRevenue,
            'today_expense' => $todayExpense,
            'present_employees' => $presentEmployees
        ];

        // 5. Tampilkan Halaman Dashboard[cite: 25]
        return view('owner.dashboard', compact('statistics'));
    }
}