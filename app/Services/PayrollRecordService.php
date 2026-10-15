<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;

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
            if ($row['days_present'] <= 0) {
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
        $record->update([
            'status'  => 'Dibayar',
            'paid_at' => Carbon::now(),
        ]);

        return $record;
    }

    /**
     * Buka kunci gaji yang sudah Dibayar (misalnya ada presensi susulan),
     * supaya bisa dihitung ulang. Status kembali ke Belum Dibayar.
     */
    public function unlock(Payroll $record): Payroll
    {
        $record->update([
            'status'  => 'Belum Dibayar',
            'paid_at' => null,
        ]);

        return $record;
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