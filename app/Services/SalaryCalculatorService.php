<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class SalaryCalculatorService
{
    public function calculateDailySalary(Attendance $attendance)
    {
        $employee = $attendance->employee;
        
        $checkIn = Carbon::parse($attendance->check_in_at);
        $checkOut = Carbon::parse($attendance->check_out_at);
        
        // Menghitung selisih jam
        $totalHours = $checkIn->diffInHours($checkOut);
        
        // Logika dasar (Asumsi kerja reguler 7 jam, sisanya dihitung lembur)
        $regularHours = $totalHours > 7 ? 7 : $totalHours;
        $overtimeHours = $totalHours > 7 ? ($totalHours - 7) : 0;

        // Kalkulasi gaji
        $dailyWage = $employee->base_daily_wage; // Gaji pokok harian
        $overtimePay = $overtimeHours * $employee->overtime_rate_1x; // Asumsi tarif lembur 1x
        
        $totalDailySalary = $dailyWage + $overtimePay;

        // Simpan hasil kalkulasi ke database sesuai Activity Diagram
        $attendance->update([
            'regular_hours' => $regularHours,
            'overtime_1x' => $overtimeHours,
            'daily_wage' => $totalDailySalary,
        ]);

        return $attendance;
    }

    /**
     * Mengambil riwayat presensi & estimasi gaji berjalan (Senin s/d Hari Ini) untuk satu Karyawan
     */
    public function getEmployeeSalaryHistory(Employee $employee)
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $today = Carbon::now()->toDateString();

        $attendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $today])
            ->orderBy('date', 'desc')
            ->get();

        $totalEstimatedSalary = $attendances->sum('daily_wage');

        return [
            'attendances' => $attendances,
            'total_estimated_salary' => $totalEstimatedSalary,
            'period' => $startOfWeek . ' s/d ' . $today
        ];
    }

    /**
     * Mengambil rekap presensi dan total gaji seluruh karyawan berdasarkan filter tanggal
     */
    public function getAllEmployeesSalaryRecap($startDate, $endDate)
    {
        $employees = Employee::with(['attendances' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        $recapData = $employees->map(function ($employee) {
            $totalRegular = $employee->attendances->sum('regular_hours');
            $totalOvertime = $employee->attendances->sum('overtime_1x') + $employee->attendances->sum('overtime_2x');
            $totalSalary = $employee->attendances->sum('daily_wage');

            return [
                'employee' => $employee,
                'total_regular_hours' => $totalRegular,
                'total_overtime_hours' => $totalOvertime,
                'total_salary' => $totalSalary
            ];
        });

        return $recapData;
    }
}