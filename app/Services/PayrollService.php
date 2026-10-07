<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Hitung jam reguler, jam lembur, dan gaji harian dari jam masuk/pulang.
     * Fungsi ini tidak menyentuh database sehingga mudah diuji.
     */
    public function calculateDay(Employee $employee, ?Carbon $checkIn, ?Carbon $checkOut): array
    {
        $zero = ['regular_hours' => 0, 'overtime_1x' => 0, 'overtime_2x' => 0, 'daily_wage' => 0];

        // Belum check-out -> belum bisa dihitung
        if (!$checkIn || !$checkOut || $checkOut->lessThanOrEqualTo($checkIn)) {
            return $zero;
        }

        $cfg = config('payroll');

        $regular = $this->overlapHours($checkIn, $checkOut,
            $this->at($checkIn, $cfg['work_start']), $this->at($checkIn, $cfg['work_end']));

        $ot1 = $this->overlapHours($checkIn, $checkOut,
            $this->at($checkIn, $cfg['overtime_1x']['start']), $this->at($checkIn, $cfg['overtime_1x']['end']));

        $ot2 = $this->overlapHours($checkIn, $checkOut,
            $this->at($checkIn, $cfg['overtime_2x']['start']), $this->at($checkIn, $cfg['overtime_2x']['end']));

        // Tarif lembur per jam: pakai nilai di data karyawan bila diisi,
        // kalau 0 diturunkan dari gaji harian / 7 jam x pengali.
        $hourly = $employee->base_daily_wage / $cfg['regular_hours'];
        $rate1  = $employee->overtime_rate_1x > 0 ? $employee->overtime_rate_1x : $hourly * $cfg['overtime_1x']['multiplier'];
        $rate2  = $employee->overtime_rate_2x > 0 ? $employee->overtime_rate_2x : $hourly * $cfg['overtime_2x']['multiplier'];

        $wage = $employee->base_daily_wage
              + $cfg['meal_allowance']
              + ($ot1 * $rate1)
              + ($ot2 * $rate2);

        return [
            'regular_hours' => round($regular, 2),
            'overtime_1x'   => round($ot1, 2),
            'overtime_2x'   => round($ot2, 2),
            'daily_wage'    => round($wage, 2),
        ];
    }

    /**
     * Hitung ulang lalu simpan ke tabel attendances.
     * Panggil ini setelah check-out atau saat owner mengoreksi presensi.
     */
    public function applyToAttendance(Attendance $attendance): Attendance
    {
        $result = $this->calculateDay(
            $attendance->employee,
            $attendance->check_in_at,
            $attendance->check_out_at
        );

        $attendance->update($result);
        return $attendance;
    }

    /**
     * Rentang periode gaji (Senin - Sabtu) dari sebuah tanggal.
     */
    public function weekRange(Carbon $date): array
    {
        $start = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end   = $start->copy()->addDays(5)->endOfDay(); // Sabtu
        return [$start, $end];
    }

    /**
     * Ringkasan gaji satu karyawan untuk satu periode.
     */
    public function summarizeEmployee(Employee $employee, Carbon $start, Carbon $end): array
    {
        $rows = $employee->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('check_in_at')
            ->get();

        $meal = $rows->where('daily_wage', '>', 0)->count() * config('payroll.meal_allowance');
        $base = $rows->where('daily_wage', '>', 0)->count() * $employee->base_daily_wage;
        $total = $rows->sum('daily_wage');

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

    /**
     * Ringkasan gaji semua karyawan untuk satu periode (untuk halaman rekap & laporan).
     */
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

    // ---------- helper ----------

    private function at(Carbon $day, string $time): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $day->copy()->setTime($h, $m, 0);
    }

    private function overlapHours(Carbon $start, Carbon $end, Carbon $from, Carbon $to): float
    {
        $s = $start->greaterThan($from) ? $start : $from;
        $e = $end->lessThan($to) ? $end : $to;

        return $e->greaterThan($s) ? $s->diffInMinutes($e) / 60 : 0.0;
    }
}