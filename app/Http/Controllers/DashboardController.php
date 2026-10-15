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
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // 1. Ambil Data Pesanan
        $activeOrders = Order::whereIn('status', ['pending', 'process'])->count();
        $completedToday = Order::where('status', 'completed')->whereDate('updated_at', $today)->count();

        // 2. Ambil Data Keuangan
        $todayRevenue = OrderDetail::whereHas('order', function ($query) use ($today) {
            $query->where('status', 'completed')
                  ->whereDate('updated_at', $today);
        })->get()->sum(function ($detail) {
            // PERBAIKAN: Tambahkan operator perkalian (*) di sini
            return $detail->quantity * $detail->unit_price; 
        });

        $todayExpense = RawMaterialExpense::whereDate('expense_date', $today)->sum('amount');

        // 3. Ambil Data Presensi
        $presentEmployees = Attendance::where('date', $today)->whereNotNull('check_in_at')->count();

        // Data Mingguan
        $weeklyOrders = Order::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $weeklyRevenue = OrderDetail::whereHas('order', function ($query) use ($weekStart, $weekEnd) {
            $query->where('status', 'completed')
                  ->whereBetween('updated_at', [$weekStart, $weekEnd]);
        })->get()->sum(function ($detail) {
            // PERBAIKAN: Tambahkan operator perkalian (*) di sini
            return $detail->quantity * $detail->unit_price; 
        });

        $recentOrders = Order::with('details')
            ->whereIn('status', ['pending', 'process'])
            ->whereDate('pickup_at', '>=', $today)
            ->orderBy('pickup_at')
            ->limit(5)
            ->get();

        // 4. Susun Data Statistik
        $statistics = [
            'active_orders'     => $activeOrders,
            'completed_today'   => $completedToday,
            'today_revenue'     => $todayRevenue,
            'today_expense'     => $todayExpense,
            'present_employees' => $presentEmployees,
            'weekly_orders'     => $weeklyOrders,
            'weekly_revenue'    => $weeklyRevenue,
        ];

        // 5. Tampilkan Halaman Dashboard
        return view('owner.dashboard', compact('statistics', 'recentOrders'));
    }

    public function employeeDashboard()
    {
        // Ambil data karyawan yang sedang login
        $employee = Auth::user()->employee;

        // CEK JIKA KARYAWAN BELUM TERHUBUNG
        if (!$employee) {
            // Logout atau berikan pesan error yang jelas
            Auth::logout();
            return redirect('/login')->withErrors([
                'username' => 'Akun Anda belum terdaftar sebagai data karyawan. Silakan hubungi Owner.'
            ]);
        }

        $today = Carbon::today()->toDateString();
        
        // Cek status presensi hari ini
        $attendanceToday = Attendance::where('employees_id', $employee->id)
                                     ->where('date', $today)
                                     ->first();

        // ==========================================
        // LOGIKA BARU UNTUK RIWAYAT DAN GAJI
        // ==========================================
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Ambil data absensi khusus minggu ini untuk menghitung ringkasan
        $weeklyAttendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();

        $totalGaji = $weeklyAttendances->sum('daily_wage');
        $totalKehadiran = $weeklyAttendances->count();
        $totalLembur = $weeklyAttendances->sum(function ($att) {
            return $att->overtime_1x + $att->overtime_2x;
        });

        // 2. Ambil riwayat absensi keseluruhan dengan Pagination (10 data per halaman)
        $paginatedAttendances = Attendance::where('employees_id', $employee->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        // 3. Susun data dalam bentuk array agar sesuai dengan panggilan di View
        $salaryData = [
            'total_gaji'      => $totalGaji,
            'total_kehadiran' => $totalKehadiran,
            'total_lembur'    => $totalLembur,
            'attendances'     => $paginatedAttendances
        ];

        // Mengarahkan ke folder resources/views/employee/dashboard.blade.php
        // Jangan lupa sertakan 'salaryData' di dalam compact()
        return view('employee.dashboard', compact('employee', 'attendanceToday', 'salaryData'));
    }
}