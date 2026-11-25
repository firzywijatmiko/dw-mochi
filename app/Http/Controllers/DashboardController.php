<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan ringkasan pesanan untuk owner berdasarkan jadwal ambil (pickup_at).
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Pesanan Aktif Hari Ini (jadwal ambil hari ini yang masih pending/process)
        $activeOrders = Order::whereIn('status', ['pending', 'process'])
            ->whereDate('pickup_at', $today)
            ->count();

        // 2. Pesanan Selesai Hari Ini (jadwal ambil hari ini yang sudah completed)
        $completedToday = Order::where('status', 'completed')
            ->whereDate('pickup_at', $today)
            ->count();

        // 3. Karyawan Hadir Hari Ini
        $presentEmployees = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in_at')
            ->distinct()
            ->count('employees_id');

        // 4. Daftar pesanan aktif yang jatuh tempo hari ini atau sudah terlambat
        $recentOrders = Order::with('details.productVariant')
            ->whereIn('status', ['pending', 'process'])
            ->whereDate('pickup_at', '<=', $today)
            ->orderBy('pickup_at', 'asc')
            ->limit(10)
            ->get();

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

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek   = $startOfWeek->copy()->addDays(5)->endOfDay();

        $weeklyAttendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();

        $sickDays = $weeklyAttendances->where('attendance_status', 'izin_sakit')
            ->where('sick_leave_status', 'approved')
            ->count();

        $salaryData = [
            'total_gaji'      => $weeklyAttendances->sum('daily_wage') + ($sickDays * config('payroll.sick_allowance', 0)),
            'total_kehadiran' => $weeklyAttendances->where('attendance_status', 'hadir')->whereNotNull('check_in_at')->count(),
            'total_lembur'    => $weeklyAttendances->sum(fn ($att) => ($att->overtime_1x ?? 0) + ($att->overtime_2x ?? 0)),
        ];

        return view('employee.dashboard', compact('employee', 'attendanceToday', 'salaryData'));
    }
}