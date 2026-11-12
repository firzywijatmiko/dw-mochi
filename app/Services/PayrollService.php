<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Hitung jam reguler, jam lembur, dan gaji harian dari jam masuk/pulang.
     */
    public function calculateDay(Employee $employee, ?Carbon $checkIn, ?Carbon $checkOut): array
    {
        $zero = [
            'worked_hours' => 0,
            'regular_hours' => 0,
            'overtime_1x' => 0,
            'overtime_2x' => 0,
            'daily_wage' => 0,
        ];

        // Belum check-out -> belum bisa dihitung
        if (!$checkIn || !$checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return $zero;
        }

                $cfg = config('payroll');

        // Hitung dalam menit bulat supaya tidak ada galat pecahan
        $minutes = $checkIn->diffInMinutes($checkOut);

        // Minimal 1 jam kerja, dicek pada durasi aktual sebelum pembulatan
        if ($minutes < $cfg['min_minutes']) {
            $zero['worked_hours'] = round($minutes / 60, 2);
            return $zero;
        }

        // Pembulatan: sisa menit >= 30 naik, di bawah itu turun
        $workedHours = intdiv($minutes, 60)
            + (($minutes % 60) >= $cfg['round_up_from_minutes'] ? 1 : 0);

        $regular  = min($workedHours, $cfg['regular_hours']);
        $overtime = max($workedHours - $cfg['regular_hours'], 0);

        // Jam lembur tetap bertingkat seperti kebijakan yang sudah ada:
        // dua jam pertama memakai tarif 1x, sisanya memakai tarif 2x.
        $ot1Limit = $this->configuredWindowHours($cfg['overtime_1x']);
        $ot1 = min($overtime, $ot1Limit);
        $ot2 = max($overtime - $ot1, 0);

        // Tarif lembur per jam
        $hourly = $employee->base_daily_wage / $cfg['regular_hours'];
        
        // PASTIKAN BARIS INI MENGGUNAKAN TANDA BINTANG (*) BUKAN MINUS
        $rate1  = $employee->overtime_rate_1x > 0 ? $employee->overtime_rate_1x : $hourly * $cfg['overtime_1x']['multiplier'];
        $rate2  = $employee->overtime_rate_2x > 0 ? $employee->overtime_rate_2x : $hourly * $cfg['overtime_2x']['multiplier'];

        $regularWage = ($employee->base_daily_wage / $cfg['regular_hours']) * $regular;
        $wage = $regularWage
              + $cfg['meal_allowance']
              + ($ot1 * $rate1)
              + ($ot2 * $rate2);

        return [
            'worked_hours'  => round($workedHours, 2),
            'regular_hours' => round($regular, 2),
            'overtime_1x'   => round($ot1, 2),
            'overtime_2x'   => round($ot2, 2),
            'daily_wage'    => round($wage, 2),
        ];
    }

    public function applyToAttendance(Attendance $attendance): Attendance
    {
        $result = $this->calculateDay(
            $attendance->employee,
            $attendance->check_in_at,
            $attendance->check_out_at
        );

        $attendance->update([
            'regular_hours' => $result['regular_hours'],
            'overtime_1x' => $result['overtime_1x'],
            'overtime_2x' => $result['overtime_2x'],
            'daily_wage' => $result['daily_wage'],
        ]);
        return $attendance;
    }

    public function weekRange(Carbon $date): array
    {
        $start = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end   = $start->copy()->addDays(5)->endOfDay(); // Sabtu
        return [$start, $end];
    }

    public function summarizeEmployee(Employee $employee, Carbon $start, Carbon $end): array
    {
        $rows = $employee->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('check_in_at')
            ->get();

                $hourly = $employee->base_daily_wage / config('payroll.regular_hours');
        $meal   = $rows->where('daily_wage', '>', 0)->count() * config('payroll.meal_allowance');
        $base   = round($rows->sum('regular_hours') * $hourly, 2);
        $total  = $rows->sum('daily_wage');

        return [
            'employee'       => $employee,
            'days_present'   => $rows->count(),
            'regular_hours'  => $rows->sum('regular_hours'),
            'overtime_1x'    => $rows->sum('overtime_1x'),
            'overtime_2x'    => $rows->sum('overtime_2x'),
            'base_pay'       => $base,
            'meal_pay'       => $meal,
            'overtime_pay'   => $total - $base - $meal,
            'total'          => $total,
        ];
    }

    public function summarizeAll(Carbon $start, Carbon $end): array
    {
        $employees = Employee::where('status', 'Aktif')
            ->orWhereHas('attendances', function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            })->get();

        $rows = $employees->map(fn ($e) => $this->summarizeEmployee($e, $start, $end));

        return [
            'rows'        => $rows,
            'total_wages' => $rows->sum('total'),
        ];
    }

    private function configuredWindowHours(array $window): float
    {
        [$startHour, $startMinute] = array_map('intval', explode(':', $window['start']));
        [$endHour, $endMinute] = array_map('intval', explode(':', $window['end']));

        return (($endHour * 60) + $endMinute - (($startHour * 60) + $startMinute)) / 60;
    }
}