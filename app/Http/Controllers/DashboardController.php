<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan ringkasan pesanan untuk owner.
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Ambil Data Pesanan (sesuai OrderController: pending, process, completed, cancelled)
        $activeOrders = Order::whereIn('status', ['pending', 'process'])->count();

        $completedToday = Order::where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        $presentEmployees = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in_at')
            ->distinct()
            ->count('employees_id');

        // 2. Daftar seluruh pesanan aktif, termasuk yang sudah lewat jadwal ambil
        $recentOrders = Order::with('details.productVariant')
            ->whereIn('status', ['pending', 'process'])
            ->orderByRaw('pickup_at IS NULL, pickup_at ASC')
            ->limit(5)
            ->get();

        // 3. Susun Data Statistik
        $statistics = [
            'active_orders'     => $activeOrders,
            'completed_today'   => $completedToday,
            'present_employees' => $presentEmployees,
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