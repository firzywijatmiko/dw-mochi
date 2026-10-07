<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class SalaryCalculatorService
{
    protected $payroll;

    public function __construct(PayrollService $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * Hitung jam reguler, lembur bertingkat (2x & 3x), dan gaji harian,
     * lalu simpan ke tabel attendances. Aturan ada di config/payroll.php.
     */
    public function calculateDailySalary(Attendance $attendance)
    {
        $result = $this->payroll->calculateDay(
            $attendance->employee,
            $attendance->check_in_at ? Carbon::parse($attendance->check_in_at) : null,
            $attendance->check_out_at ? Carbon::parse($attendance->check_out_at) : null
        );

        $attendance->update($result);

        return $attendance;
    }

    /**
     * Riwayat presensi & estimasi gaji berjalan (Senin s/d hari ini) untuk satu karyawan.
     */
    public function getEmployeeSalaryHistory(Employee $employee)
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $today = Carbon::now()->toDateString();

        $attendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $today])
            ->orderBy('date', 'desc')
            ->get();

        return [
            'attendances'            => $attendances,
            'total_estimated_salary' => $attendances->sum('daily_wage'),
            'period'                 => $startOfWeek . ' s/d ' . $today,
        ];
    }

    /**
     * Rekap presensi & total gaji seluruh karyawan berdasarkan filter tanggal.
     * Key lama dipertahankan, ditambah rincian lembur per tingkat.
     */
    public function getAllEmployeesSalaryRecap($startDate, $endDate)
    {
        $employees = Employee::with(['attendances' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();

        return $employees->map(function ($employee) {
            $att = $employee->attendances;

            return [
                'employee'             => $employee,
                'days_present'         => $att->whereNotNull('check_in_at')->count(),
                'total_regular_hours'  => $att->sum('regular_hours'),
                'overtime_1x'          => $att->sum('overtime_1x'),
                'overtime_2x'          => $att->sum('overtime_2x'),
                'total_overtime_hours' => $att->sum('overtime_1x') + $att->sum('overtime_2x'),
                'total_salary'         => $att->sum('daily_wage'),
            ];
        });
    }
}