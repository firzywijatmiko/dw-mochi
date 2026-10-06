<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RawMaterialExpense;
use App\Models\Attendance;
use App\Models\OrderDetail; // Pastikan model OrderDetail dipanggil
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mengambil dan menyusun statistik data pesanan, keuangan, dan presensi
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Ambil Data Pesanan
        $activeOrders = Order::whereIn('status', ['pending', 'process'])->count();
        $completedToday = Order::where('status', 'completed')->whereDate('updated_at', $today)->count();

        // 2. Ambil Data Keuangan
        // PERBAIKAN: Menghitung total pendapatan dari tabel order_details (quantity * unit_price)
        $todayRevenue = OrderDetail::whereHas('order', function ($query) use ($today) {
            $query->where('status', 'completed')
                  ->whereDate('updated_at', $today);
        })->get()->sum(function ($detail) {
            return $detail->quantity * $detail->unit_price;
        });

        $todayExpense = RawMaterialExpense::whereDate('expense_date', $today)->sum('amount');

        // 3. Ambil Data Presensi
        $presentEmployees = Attendance::where('date', $today)->whereNotNull('check_in_at')->count();

        // 4. Susun Data Statistik
        $statistics = [
            'active_orders' => $activeOrders,
            'completed_today' => $completedToday,
            'today_revenue' => $todayRevenue,
            'today_expense' => $todayExpense,
            'present_employees' => $presentEmployees
        ];

        // 5. Tampilkan Halaman Dashboard
        return view('owner.dashboard', compact('statistics'));
    }
}