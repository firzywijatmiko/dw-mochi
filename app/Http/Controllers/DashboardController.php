<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RawMaterialExpense;
use App\Models\Attendance;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mengambil dan menyusun statistik data pesanan, keuangan, dan presensi
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $weekStart = Carbon::now()->startOfWeek()->startOfDay();
        $weekEnd = Carbon::now()->endOfWeek()->endOfDay();

        // 1. Ambil Data Pesanan (sesuai OrderController: pending, process, completed, cancelled)
        $activeOrders = Order::whereIn('status', ['pending', 'process'])->count();

        $completedToday = Order::where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        // 2. Ambil Data Keuangan (sesuai OrderDetail: unit_price)
        $todayRevenue = OrderDetail::whereHas('order', function ($query) use ($today) {
            $query->where('status', 'completed')
                  ->whereDate('updated_at', $today);
        })->get()->sum(function ($detail) {
            return $detail->quantity * $detail->unit_price;
        });

        $todayExpense = RawMaterialExpense::whereDate('expense_date', $today)->sum('amount');

        // 3. Ambil Data Presensi Hari Ini
        $presentEmployees = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in_at')
            ->count();

        // 4. Data Mingguan
        $weeklyOrders = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $weeklyRevenue = OrderDetail::whereHas('order', function ($query) use ($weekStart, $weekEnd) {
            $query->where('status', 'completed')
                  ->whereBetween('updated_at', [$weekStart, $weekEnd]);
        })->get()->sum(function ($detail) {
            return $detail->quantity * $detail->unit_price;
        });

        // 5. Daftar Pesanan Hari Ini & Mendatang
        $recentOrders = Order::with('details.productVariant')
            ->whereIn('status', ['pending', 'process'])
            ->where(function ($query) use ($today) {
                $query->whereDate('pickup_at', '>=', $today)
                      ->orWhereNull('pickup_at');
            })
            ->orderByRaw('pickup_at IS NULL, pickup_at ASC')
            ->limit(10)
            ->get();

        // 6. Susun Data Statistik
        $statistics = [
            'active_orders'     => $activeOrders,
            'completed_today'   => $completedToday,
            'today_revenue'     => $todayRevenue,
            'today_expense'     => $todayExpense,
            'present_employees' => $presentEmployees,
            'weekly_orders'     => $weeklyOrders,
            'weekly_revenue'    => $weeklyRevenue,
        ];

        return view('owner.dashboard', compact('statistics', 'recentOrders'));
    }

    public function employeeDashboard()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            Auth::logout();
            return redirect('/login')->withErrors([
                'username' => 'Akun Anda belum terdaftar sebagai data karyawan. Silakan hubungi Owner.'
            ]);
        }

        $today = Carbon::today()->toDateString();

        $attendanceToday = Attendance::where('employees_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        $startOfWeek = Carbon::now()->startOfWeek()->startOfDay();
        $endOfWeek = Carbon::now()->endOfWeek()->endOfDay();

        $weeklyAttendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();

        $totalGaji = $weeklyAttendances->sum('daily_wage');
        $totalKehadiran = $weeklyAttendances->where('attendance_status', '!=', 'alpa')->count();
        $totalLembur = $weeklyAttendances->sum(function ($att) {
            return $att->overtime_1x + $att->overtime_2x;
        });

        $paginatedAttendances = Attendance::where('employees_id', $employee->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        $salaryData = [
            'total_gaji'      => $totalGaji,
            'total_kehadiran' => $totalKehadiran,
            'total_lembur'    => $totalLembur,
            'attendances'     => $paginatedAttendances
        ];

        return view('employee.dashboard', compact('employee', 'attendanceToday', 'salaryData'));
    }
}