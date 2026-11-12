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
        
        // Ambil data presensi minggu ini atau sesuai kebutuhan aplikasimu
        $attendancesList = \App\Models\Attendance::where('employees_id', $employee->id)
            ->where('date', '>=', Carbon::now()->subYear()->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        // Hitung total kehadiran, lembur, dan total gaji secara aman
        $totalKehadiran = $attendancesList->where('attendance_status', '!=', 'alpa')->count();
        $totalLembur = $attendancesList->sum(function($att) {
            return ($att->overtime_1x ?? 0) + ($att->overtime_2x ?? 0);
        });
        
        // Hitung total gaji berdasarkan gaji harian dan lembur (atau sesuaikan dengan service gaji yang sudah ada di aplikasimu)
        $totalGaji = $attendancesList->sum(function($att) {
            return $att->daily_wage ?? 0;
        });

        $salaryData = [
            'total_gaji'      => $totalGaji,
            'total_kehadiran' => $totalKehadiran,
            'total_lembur'    => $totalLembur,
            'attendances'     => $attendancesList
        ];

        return view('employee.salary_history', compact('salaryData'));
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

        $attendances = $attendancesQuery
            ->orderBy('date', 'desc')
            ->orderBy('check_in_at')
            ->get();

        $summary = [
            'present' => $attendances->where('attendance_status', 'hadir')->whereNotNull('check_in_at')->count(),
            'sick_leave' => $attendances->where('attendance_status', 'izin_sakit')->count(),
            'absent' => $attendances->where('attendance_status', 'alpa')->count(),
            'outside' => $attendances->where('location_status', 'Luar Radius')->count(),
        ];

        $employees = Employee::orderBy('name')->get();

        return view('owner.attendances.recap', compact(
            'startDate',
            'endDate',
            'attendances',
            'summary',
            'employees'
        ));
    }
}