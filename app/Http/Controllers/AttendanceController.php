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

    /**
     * ACTIVITY DIAGRAM: LIHAT RIWAYAT DAN TOTAL GAJI (KARYAWAN)[cite: 23]
     */
    public function salaryHistory()
    {
        $employee = Auth::user()->employee;
        
        // Ambil data presensi minggu ini atau sesuai kebutuhan aplikasimu
        $attendancesList = \App\Models\Attendance::where('employees_id', $employee->id)
            ->orderBy('date', 'desc')
            ->get();

        // Hitung total kehadiran, lembur, dan total gaji secara aman
        $totalKehadiran = $attendancesList->count();
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
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $attendances = Attendance::with('employee')
            ->whereDate('date', $date->toDateString())
            ->orderBy('check_in_at')
            ->get();

        $totalEmployees = Employee::where('status', 'Aktif')->count();
        $present = $attendances->whereNotNull('check_in_at')->count();

        $summary = [
            'present' => $present,
            'absent'  => max($totalEmployees - $present, 0),
            'outside' => $attendances->where('location_status', 'Luar Radius')->count(),
        ];

        return view('owner.attendances.recap', compact('date', 'attendances', 'summary'));
    }
}