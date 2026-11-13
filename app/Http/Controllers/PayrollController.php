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

    public function index(Request $request)
    {
        $this->recordService->syncFromAttendances();

            $retentionLimit = Carbon::now()->subYear()->toDateString();

            $query = Payroll::with('employee')
                ->where('period_start', '>=', $retentionLimit)
                ->orderByDesc('period_start')
                ->orderBy('employees_id');

        // Filter bulan (format Y-m)
        $month = $request->input('month');
        $selectedEmployee = $request->input('employee_id');

        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $query->whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$month]);
        } else {
            $month = null;
        }

        if ($selectedEmployee) {
            $query->where('employees_id', $selectedEmployee);
        }

        $payrolls = $query->paginate(25)->withQueryString();

        // Hitung selisih presensi untuk item yang tampil
        $payrolls->getCollection()->each(function ($p) {
            $p->current_total = $this->recordService->currentTotal($p);
            $p->difference    = round($p->current_total - $p->total_pay, 2);
        });

        // Untuk opsi dropdown filter bulan (ambil dari seluruh data unik)
        $allPayrolls = Payroll::where('period_start', '>=', $retentionLimit)
            ->orderByDesc('period_start')->get();
        $monthOptions = $allPayrolls->map(function ($p) {
            return $p->period_start->format('Y-m');
        })->unique()->values()->mapWithKeys(function ($m) {
            return [$m => Carbon::parse($m . '-01')->locale('id')->translatedFormat('F Y')];
        });

        $employees   = Employee::orderBy('name')->get(['id', 'name']);
        $totalUnpaid = $this->recordService->totalUnpaid();

        return view('owner.payrolls.index', compact(
            'payrolls', 'employees', 'monthOptions', 'month', 'selectedEmployee', 'totalUnpaid'
        ));
    }

    public function updateStatus(Request $request, Payroll $payroll)
    {
        $request->validate(['status' => 'required|in:Belum Dibayar,Dibayar']);

        // Kunci permanen: gaji yang sudah dibayar tidak bisa dikembalikan
        if ($payroll->status === 'Dibayar') {
            return back()->withErrors([
                'status' => 'Gaji yang sudah dibayar terkunci dan tidak dapat dikembalikan ke Belum Dibayar.',
            ]);
        }

        if ($request->status === 'Dibayar') {
            $this->recordService->markAsPaid($payroll);

            return back()->with('success', 'Gaji ' . ($payroll->employee->name ?? '') . ' ditandai sudah dibayar dan slip dibuat.');
        }

        return back();
    }
}