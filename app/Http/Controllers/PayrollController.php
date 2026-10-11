<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollRecordService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    protected $payrollService;
    protected $recordService;

    public function __construct(PayrollService $payrollService, PayrollRecordService $recordService)
    {
        $this->payrollService = $payrollService;
        $this->recordService  = $recordService;
    }

    /**
     * Halaman Penggajian (per periode) dan Riwayat Gaji (jika karyawan dipilih).
     */
    public function index(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        [$start, $end] = $this->payrollService->weekRange($date);

        $employeeId = $request->input('employee_id');

        $query = Payroll::with('employee');

        if ($employeeId) {
            // Riwayat gaji satu karyawan: semua periode
            $query->where('employees_id', $employeeId)->orderByDesc('period_start');
        } else {
            // Penggajian satu periode: semua karyawan
            $query->where('period_start', $start->toDateString())
                  ->where('period_end', $end->toDateString())
                  ->orderBy('employees_id');
        }

        $payrolls   = $query->get();

        // Bandingkan angka tersimpan dengan presensi terkini (deteksi presensi susulan)
        $payrolls->each(function ($p) {
            $p->current_total = $this->recordService->currentTotal($p);
            $p->difference    = round($p->current_total - $p->total_pay, 2);
        });

        $employees  = Employee::orderBy('name')->get(['id', 'name']);
        $totalUnpaid = $this->recordService->totalUnpaid();

        return view('owner.payrolls.index', compact(
            'payrolls', 'employees', 'employeeId', 'date', 'start', 'end', 'totalUnpaid'
        ));
    }

    /**
     * Hitung/perbarui data gaji satu periode dari presensi.
     */
    public function generate(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        [$start, $end] = $this->payrollService->weekRange(Carbon::parse($request->date));
        $result = $this->recordService->generate($start, $end);

        $message = $result['updated'] . ' data gaji dihitung dari presensi.';
        if ($result['skipped_paid'] > 0) {
            $message .= ' ' . $result['skipped_paid'] . ' data berstatus Dibayar tidak diubah.';
        }

        return redirect()
            ->route('owner.payrolls.index', ['date' => $start->toDateString()])
            ->with('success', $message);
    }

    /**
     * Tandai gaji sudah dibayar.
     */
    public function markPaid(Payroll $payroll)
    {
        $this->recordService->markAsPaid($payroll);

        return back()->with('success', 'Gaji ' . ($payroll->employee->name ?? '') . ' ditandai sudah dibayar.');
    }

    /**
     * Buka kunci gaji yang sudah Dibayar agar bisa dihitung ulang.
     */
    public function unlock(Payroll $payroll)
    {
        if ($payroll->status !== 'Dibayar') {
            return back()->withErrors(['payroll' => 'Gaji ini belum berstatus Dibayar.']);
        }

        $this->recordService->unlock($payroll);

        return back()->with('success', 'Kunci gaji ' . ($payroll->employee->name ?? '') . ' dibuka. Klik "Hitung Gaji" untuk memperbarui dari presensi.');
    }
}