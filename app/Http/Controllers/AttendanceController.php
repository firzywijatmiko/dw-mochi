<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Services\LocationValidationService;
use App\Services\SalaryCalculatorService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Employee;

class AttendanceController extends Controller
{
    protected $locationService;
    protected $salaryService;

    public function __construct(LocationValidationService $locationService, SalaryCalculatorService $salaryService)
    {
        $this->locationService = $locationService;
        $this->salaryService = $salaryService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        // Hitung Jarak Perangkat Karyawan dengan Titik Produksi[cite: 18]
        if (!$this->locationService->isWithinRadius($request->latitude, $request->longitude)) {
            return response()->json(['error' => 'Anda Berada Diluar Area Produksi'], 403);
        }

        $employeeId = Auth::user()->employee->id;
        $today = Carbon::today()->toDateString();

        // Mencegah Check In ganda pada hari yang sama
        $attendance = Attendance::firstOrCreate(
            ['employees_id' => $employeeId, 'date' => $today],
            [
                'check_in_at' => Carbon::now(),
                'check_in_lat' => $request->latitude,
                'check_in_lng' => $request->longitude,
            ]
        );

        return response()->json(['success' => 'Check In Berhasil']);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        // Validasi Radius[cite: 18]
        if (!$this->locationService->isWithinRadius($request->latitude, $request->longitude)) {
            return response()->json(['error' => 'Anda Berada Diluar Area Produksi'], 403);
        }

        $employeeId = Auth::user()->employee->id;
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employees_id', $employeeId)->where('date', $today)->first();

        if (!$attendance || $attendance->check_out_at) {
            return response()->json(['error' => 'Anda belum Check In atau sudah Check Out hari ini'], 400);
        }

        // Update waktu pulang
        $attendance->update([
            'check_out_at' => Carbon::now(),
            'check_out_lat' => $request->latitude,
            'check_out_lng' => $request->longitude,
        ]);

        // Kalkulasi Total Gaji dan Simpan ke Database[cite: 18]
        $this->salaryService->calculateDailySalary($attendance);

        return response()->json(['success' => 'Check Out Berhasil. Gaji hari ini telah dikalkulasi.']);
    }

    public function sickLeave(Request $request)
    {
        $request->validate([
            'sick_leave_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'sick_leave_proof.required' => 'Bukti foto wajib diunggah.',
            'sick_leave_proof.image' => 'Bukti harus berupa file gambar.',
            'sick_leave_proof.max' => 'Ukuran bukti foto maksimal 5 MB.',
        ]);

        $employeeId = Auth::user()->employee->id;
        $today = Carbon::today()->toDateString();

        if (Attendance::where('employees_id', $employeeId)->where('date', $today)->exists()) {
            return back()->withErrors(['sick_leave' => 'Presensi atau izin hari ini sudah tercatat.']);
        }

        $proofPath = $request->file('sick_leave_proof')->store('sick-leaves', 'public');

        Attendance::create([
            'employees_id' => $employeeId,
            'date' => $today,
            'attendance_status' => 'izin_sakit',
            'sick_leave_proof' => $proofPath,
            'sick_leave_status' => 'pending',
        ]);

        return back()->with('success', 'Izin sakit berhasil dikirim.');
    }

    public function approveSickLeave(Attendance $attendance)
    {
        if ($attendance->attendance_status !== 'izin_sakit' || $attendance->sick_leave_status !== 'pending') {
            return back()->withErrors(['sick_leave' => 'Pengajuan izin ini sudah diproses atau tidak valid.']);
        }

        $attendance->update(['sick_leave_status' => 'approved']);

        return back()->with('success', 'Izin sakit berhasil disetujui.');
    }

    public function rejectSickLeave(Attendance $attendance)
    {
        if ($attendance->attendance_status !== 'izin_sakit' || $attendance->sick_leave_status !== 'pending') {
            return back()->withErrors(['sick_leave' => 'Pengajuan izin ini sudah diproses atau tidak valid.']);
        }

        $attendance->update([
            'attendance_status' => 'alpa',
            'sick_leave_status' => 'rejected',
            'regular_hours' => 0,
            'overtime_1x' => 0,
            'overtime_2x' => 0,
            'daily_wage' => 0,
        ]);

        return back()->with('success', 'Izin ditolak dan otomatis dihitung sebagai Alpa/Tanpa Keterangan.');
    }

    /**
     * ACTIVITY DIAGRAM: LIHAT RIWAYAT DAN TOTAL GAJI (KARYAWAN)[cite: 23]
     */
    public function salaryHistory()
    {
        $employee = Auth::user()->employee;

        // 1. Data khusus minggu berjalan (Senin - Sabtu) untuk Banner Navy di atas
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $currentWeekEnd   = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(5)->toDateString();

        $currentWeekAttendances = Attendance::where('employees_id', $employee->id)
            ->whereBetween('date', [$currentWeekStart, $currentWeekEnd])
            ->get();

        $currentSickDays = $currentWeekAttendances->where('attendance_status', 'izin_sakit')
            ->where('sick_leave_status', 'approved')
            ->count();

        $salaryData = [
            'period_label'    => Carbon::parse($currentWeekStart)->translatedFormat('d M') . ' - ' . Carbon::parse($currentWeekEnd)->translatedFormat('d M Y'),
            'total_gaji'      => $currentWeekAttendances->sum('daily_wage') + ($currentSickDays * config('payroll.sick_allowance', 0)),
            'total_kehadiran' => $currentWeekAttendances->where('attendance_status', 'hadir')->whereNotNull('check_in_at')->count(),
            'total_lembur'    => $currentWeekAttendances->sum(fn ($att) => ($att->overtime_1x ?? 0) + ($att->overtime_2x ?? 0)),
        ];

        // 2. Rekap per minggu (1 tahun terakhir) + sinkronisasi dengan Slip Gaji
        $attendancesList = Attendance::where('employees_id', $employee->id)
            ->where('date', '>=', Carbon::now()->subYear()->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        $payslips = \App\Models\Payslip::where('employees_id', $employee->id)
            ->get()
            ->keyBy(fn ($p) => $p->period_start->toDateString());

        $grouped = $attendancesList->groupBy(function ($att) {
            return $att->date->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        });

        foreach ($payslips as $startKey => $slip) {
            if (!$grouped->has($startKey)) {
                $grouped->put($startKey, collect());
            }
        }

        $weeklyPeriods = $grouped->sortKeysDesc()->map(function ($items, $weekStartStr) use ($payslips) {
            $start = Carbon::parse($weekStartStr);
            $end   = $start->copy()->addDays(5);
            $slip  = $payslips->get($weekStartStr);

            $daysPresent = $items->where('attendance_status', 'hadir')->whereNotNull('check_in_at')->count();
            $sickDays    = $items->where('attendance_status', 'izin_sakit')->where('sick_leave_status', 'approved')->count();
            $overtime    = $items->sum(fn ($a) => ($a->overtime_1x ?? 0) + ($a->overtime_2x ?? 0));
            $estWage     = $items->sum('daily_wage') + ($sickDays * config('payroll.sick_allowance', 0));

            return [
                'period_label' => $start->translatedFormat('d M Y') . ' – ' . $end->translatedFormat('d M Y'),
                'is_current'   => $weekStartStr === Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'is_paid'      => (bool) $slip,
                'paid_at'      => $slip ? $slip->paid_at : null,
                'total_pay'    => $slip ? $slip->total_pay : $estWage,
                'days_present' => $slip ? $slip->days_present : $daysPresent,
                'sick_days'    => $sickDays,
                'overtime'     => $overtime,
                'payslip'      => $slip,
                'attendances'  => $items->sortByDesc('date')->values(),
            ];
        })->values();

        return view('employee.salary_history', compact('salaryData', 'weeklyPeriods'));
    }

        /**
     * ACTIVITY DIAGRAM: REKAP PRESENSI & GAJI (OWNER)[cite: 24]
     */
    public function recap(Request $request)
    {
        $retentionLimit = Carbon::now()->subYear()->toDateString();

        $request->validate([
            'start_date' => 'nullable|date|after_or_equal:' . $retentionLimit,
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'employee_id' => 'nullable|integer|exists:employees,id',
        ], [
            'start_date.after_or_equal' => 'Data presensi hanya ditampilkan untuk 1 tahun terakhir.',
        ]);

        $perPage = in_array((int) $request->input('per_page'), [5, 10, 25, 50])
            ? (int) $request->input('per_page')
            : 10;

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : $startDate->copy()->endOfDay();

        $attendancesQuery = Attendance::with('employee')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('employee_id')) {
            $attendancesQuery->where('employees_id', $request->employee_id);
        }

        // Ambil seluruh koleksi hasil filter untuk menghitung summary (mendukung accessor model)
        $allFilteredAttendances = (clone $attendancesQuery)->get();

        $summary = [
            'present'    => $allFilteredAttendances->where('attendance_status', 'hadir')->whereNotNull('check_in_at')->count(),
            'sick_leave' => $allFilteredAttendances->where('attendance_status', 'izin_sakit')->count(),
            'absent'     => $allFilteredAttendances->where('attendance_status', 'alpa')->count(),
            'outside'    => $allFilteredAttendances->where('location_status', 'Luar Radius')->count(),
        ];

        $attendances = $attendancesQuery
            ->orderBy('date', 'desc')
            ->orderBy('check_in_at')
            ->paginate($perPage)
            ->withQueryString();

        $employees = Employee::orderBy('name')->get();

        return view('owner.attendances.recap', compact(
            'startDate',
            'endDate',
            'attendances',
            'summary',
            'employees',
            'perPage'
        ));
    }
}