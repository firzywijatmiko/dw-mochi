<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayslipDemoSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::where('name', 'Widyawati')->first();
        $owner = User::where('role', 'Owner')->first();

        if (!$employee) {
            return;
        }

        $periodStart = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
        $periodEnd = $periodStart->copy()->addDays(5);
        $payrollService = app(PayrollService::class);

        $workDays = [
            [$periodStart, '08:00', '16:00'],
            [$periodStart->copy()->addDay(), '08:00', '17:00'],
            [$periodStart->copy()->addDays(2), '08:00', '18:30'],
            [$periodStart->copy()->addDays(3), '08:00', '16:30'],
            [$periodStart->copy()->addDays(4), '08:00', '16:00'],
        ];

        foreach ($workDays as [$date, $checkInTime, $checkOutTime]) {
            $checkIn = $date->copy()->setTimeFromTimeString($checkInTime);
            $checkOut = $date->copy()->setTimeFromTimeString($checkOutTime);
            $calculated = $payrollService->calculateDay($employee, $checkIn, $checkOut);

            Attendance::updateOrCreate(
                [
                    'employees_id' => $employee->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'attendance_status' => 'hadir',
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,
                    'regular_hours' => $calculated['regular_hours'],
                    'overtime_1x' => $calculated['overtime_1x'],
                    'overtime_2x' => $calculated['overtime_2x'],
                    'daily_wage' => $calculated['daily_wage'],
                ]
            );
        }

        $sickDate = $periodStart->copy()->addDays(5);
        Attendance::updateOrCreate(
            [
                'employees_id' => $employee->id,
                'date' => $sickDate->toDateString(),
            ],
            [
                'attendance_status' => 'izin_sakit',
                'sick_leave_status' => 'approved',
                'check_in_at' => null,
                'check_out_at' => null,
                'regular_hours' => 0,
                'overtime_1x' => 0,
                'overtime_2x' => 0,
                'daily_wage' => 0,
            ]
        );

        $summary = $payrollService->summarizeEmployee(
            $employee,
            $periodStart,
            $periodEnd->copy()->endOfDay()
        );

        $paidAt = Carbon::now()->subDay();

        $payroll = Payroll::updateOrCreate(
            [
                'employees_id' => $employee->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'days_present' => $summary['days_present'],
                'base_pay' => $summary['base_pay'],
                'meal_pay' => $summary['meal_pay'],
                'overtime_pay' => $summary['overtime_pay'],
                'sick_pay' => $summary['sick_pay'],
                'total_pay' => $summary['total'],
                'status' => 'Dibayar',
                'paid_at' => $paidAt,
                'paid_by' => $owner ? $owner->id : null,
            ]
        );

        Payslip::updateOrCreate(
            ['payroll_id' => $payroll->id],
            [
                'employees_id' => $employee->id,
                'period_start' => $payroll->period_start,
                'period_end' => $payroll->period_end,
                'days_present' => $payroll->days_present,
                'base_pay' => $payroll->base_pay,
                'meal_pay' => $payroll->meal_pay,
                'overtime_pay' => $payroll->overtime_pay,
                'sick_pay' => $payroll->sick_pay,
                'total_pay' => $payroll->total_pay,
                'paid_at' => $paidAt,
            ]
        );
    }
}
