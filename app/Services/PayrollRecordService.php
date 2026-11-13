<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;
use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan hasil perhitungan gaji per periode ke tabel payrolls (F12 - Riwayat Penggajian).
 * Perhitungannya sendiri tetap dikerjakan PayrollService.
 */
class PayrollRecordService
{
    protected $payroll;

    public function __construct(PayrollService $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * Hitung gaji semua karyawan untuk satu periode dari data presensi, lalu simpan.
     * Data yang sudah berstatus Dibayar tidak diubah (riwayat pembayaran dikunci).
     */
    public function generate(Carbon $start, Carbon $end): array
    {
        $summary = $this->payroll->summarizeAll($start, $end);

        $updated = 0;
        $skippedPaid = 0;

        foreach ($summary['rows'] as $row) {
            if ($row['days_present'] <= 0 && $row['sick_pay'] <= 0) {
                continue;
            }

            $record = Payroll::firstOrNew([
                'employees_id' => $row['employee']->id,
                'period_start' => $start->toDateString(),
                'period_end'   => $end->toDateString(),
            ]);

            if ($record->exists && $record->status === 'Dibayar') {
                $skippedPaid++;
                continue;
            }

            $record->fill([
                'days_present' => $row['days_present'],
                'base_pay'     => $row['base_pay'],
                'meal_pay'     => $row['meal_pay'],
                'overtime_pay' => $row['overtime_pay'],
                'sick_pay'     => $row['sick_pay'],
                'total_pay'    => $row['total'],
            ])->save();

            $updated++;
        }

        return ['updated' => $updated, 'skipped_paid' => $skippedPaid];
    }

    /**
     * Sinkronkan data gaji dari presensi untuk beberapa minggu terakhir.
     * Gaji berstatus Dibayar tidak diubah. Dipanggil saat halaman Penggajian dibuka.
     */
    public function syncFromAttendances(int $maxWeeks = 12): void
    {
        $first = Attendance::whereNotNull('check_in_at')->min('date');

        if (!$first) {
            return;
        }

        $cursor = Carbon::parse($first)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $limit  = Carbon::now()->subWeeks($maxWeeks)->startOfWeek(Carbon::MONDAY)->startOfDay();

        if ($cursor->lt($limit)) {
            $cursor = $limit;
        }

        $today = Carbon::now();

        while ($cursor->lte($today)) {
            [$start, $end] = $this->payroll->weekRange($cursor);
            $this->generate($start, $end);
            $cursor->addWeek();
        }
    }

        public function markAsPaid(Payroll $record): Payroll
    {
        return DB::transaction(function () use ($record) {
            $record = Payroll::lockForUpdate()->findOrFail($record->id);

            if ($record->status === 'Dibayar') {
                return $record;
            }

            $record->update([
                'status'  => 'Dibayar',
                'paid_at' => Carbon::now(),
                'paid_by' => Auth::id(),
            ]);

            // Arsip slip: snapshot nilai saat dibayar
            Payslip::updateOrCreate(
                ['payroll_id' => $record->id],
                [
                    'employees_id' => $record->employees_id,
                    'period_start' => $record->period_start,
                    'period_end'   => $record->period_end,
                    'days_present' => $record->days_present,
                    'base_pay'     => $record->base_pay,
                    'meal_pay'     => $record->meal_pay,
                    'overtime_pay' => $record->overtime_pay,
                    'sick_pay'     => $record->sick_pay,
                    'total_pay'    => $record->total_pay,
                    'paid_at'      => $record->paid_at,
                ]
            );

            return $record;
        });
    }

    /**
     * Total gaji periode tersebut berdasarkan presensi saat ini
     * (untuk membandingkan dengan angka yang tersimpan).
     */
    public function currentTotal(Payroll $record): float
    {
        if (!$record->employee) {
            return (float) $record->total_pay;
        }

        $summary = $this->payroll->summarizeEmployee(
            $record->employee,
            $record->period_start->copy()->startOfDay(),
            $record->period_end->copy()->endOfDay()
        );

        return (float) $summary['total'];
    }

    public function totalUnpaid(): float
    {
        return (float) Payroll::where('status', 'Belum Dibayar')->sum('total_pay');
    }
}