<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Services\LocationValidationService;
use App\Services\SalaryCalculatorService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        
        // Ambil data presensi & lembur dari database (Senin s/d Hari Ini) lalu kalkulasi[cite: 23]
        $salaryData = $this->salaryService->getEmployeeSalaryHistory($employee);

        // Tampilkan halaman riwayat presensi & total gaji sementara[cite: 23]
        return view('karyawan.salary_history', compact('salaryData'));
    }

    /**
     * ACTIVITY DIAGRAM: REKAP PRESENSI & GAJI (OWNER)[cite: 24]
     */
    public function recap(Request $request)
    {
        // Pilih filter tanggal (default: minggu ini jika tidak ada input)[cite: 24]
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->toDateString());

        // Ambil data presensi seluruh karyawan dari database dan kalkulasi[cite: 24]
        $recapData = $this->salaryService->getAllEmployeesSalaryRecap($startDate, $endDate);

        // Tampilkan halaman rekap presensi & nominal gaji[cite: 24]
        return view('owner.attendances.recap', compact('recapData', 'startDate', 'endDate'));
    }
}