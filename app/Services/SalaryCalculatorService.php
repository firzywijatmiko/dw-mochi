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
     * Hitung jam reguler, lembur bertingkat, dan gaji harian.
     */
    public function calculateDailySalary(Attendance $attendance)
    {
        // Langsung panggil logika pintar dari PayrollService
        return $this->payroll->applyToAttendance($attendance);
    }

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

    public function getAllEmployeesSalaryRecap($startDate, $endDate)
    {
        return $this->payroll->summarizeAll(Carbon::parse($startDate), Carbon::parse($endDate));
    }
}