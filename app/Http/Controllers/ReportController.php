<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportGeneratorService $reportService)
    {
        $this->reportService = $reportService;
    }

    // --- ACTIVITY DIAGRAM: LIHAT LAPORAN OPERASIONAL ---
    public function weeklyReport(Request $request)
    {
        // Tentukan rentang waktu laporan (default: 1 minggu terakhir)
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->toDateString());

        // Ambil data kalkulasi[cite: 22]
        $reportData = $this->reportService->generateWeeklyReport($startDate, $endDate);

        // Jika request meminta download PDF[cite: 22]
        if ($request->has('download')) {
            $pdf = Pdf::loadView('owner.reports.weekly_pdf', compact('reportData'))
                      ->setPaper('A4', 'portrait');
            return $pdf->download('Laporan_Operasional_DW_Mochi_'.$startDate.'_sd_'.$endDate.'.pdf');
        }

        // Tampilkan Halaman Laporan Operasional[cite: 22]
        return view('owner.reports.weekly', compact('reportData', 'startDate', 'endDate'));
    }
}