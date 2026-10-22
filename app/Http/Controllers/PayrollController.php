<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollRecordService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected $recordService;

    public function __construct(PayrollRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * Halaman Penggajian: daftar gaji per periode, filter bulan,
     * dan riwayat gaji per karyawan (modal).
     */
    public function index(Request $request)
    {
        // Data gaji selalu disinkronkan dari presensi (yang sudah Dibayar tidak diubah)
        $this->recordService->syncFromAttendances();

        $all = Payroll::with('employee')
            ->orderByDesc('period_start')
            ->orderBy('employees_id')
            ->get();

        // Selisih angka tersimpan vs presensi terkini
        $all->each(function ($p) {
            $p->current_total = $this->recordService->currentTotal($p);
            $p->difference    = round($p->current_total - $p->total_pay, 2);
        });

        // Filter bulan (format Y-m)
        $month = $request->input('month');
        $payrolls = $all;
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $payrolls = $all->filter(function ($p) use ($month) {
                return $p->period_start->format('Y-m') === $month;
            })->values();
        } else {
            $month = null;
        }

        $monthOptions = $all->map(function ($p) {
            return $p->period_start->format('Y-m');
        })->unique()->values()->mapWithKeys(function ($m) {
            return [$m => Carbon::parse($m . '-01')->locale('id')->translatedFormat('F Y')];
        });

        // Data riwayat untuk modal per karyawan
        $histories = $all->groupBy('employees_id')->map(function ($rows) {
            return $rows->map(function ($p) {
                return [
                    'id'       => $p->id,
                    'period'   => $p->period_label,
                    'base'     => 'Rp ' . number_format($p->base_pay + $p->meal_pay, 0, ',', '.'),
                    'overtime' => 'Rp ' . number_format($p->overtime_pay, 0, ',', '.'),
                    'total'    => 'Rp ' . number_format($p->total_pay, 0, ',', '.'),
                    'status'   => $p->status_label,
                    'paid'     => $p->status === 'Dibayar',
                ];
            })->values();
        });

        $employees     = Employee::orderBy('name')->get(['id', 'name']);
        $employeeNames = $employees->pluck('name', 'id');
        $totalUnpaid   = $this->recordService->totalUnpaid();

        return view('owner.payrolls.index', compact(
            'payrolls', 'employees', 'employeeNames', 'histories', 'monthOptions', 'month', 'totalUnpaid'
        ));
    }

    /**
     * Ubah status gaji lewat dropdown (Belum Dibayar <-> Dibayar).
     * Dari Dibayar ke Belum Dibayar = buka kunci, sehingga bisa dihitung ulang dari presensi.
     */
    public function updateStatus(Request $request, Payroll $payroll)
    {
        $request->validate(['status' => 'required|in:Belum Dibayar,Dibayar']);

        if ($request->status === $payroll->status) {
            return back();
        }

        if ($request->status === 'Dibayar') {
            $this->recordService->markAsPaid($payroll);
            $message = 'Gaji ' . ($payroll->employee->name ?? '') . ' ditandai sudah dibayar.';
        } else {
            $this->recordService->unlock($payroll);
            $message = 'Status gaji ' . ($payroll->employee->name ?? '') . ' dikembalikan ke belum dibayar dan dihitung ulang dari presensi.';
        }

        return back()->with('success', $message);
    }
}