<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
        $rules = ['start_date' => 'nullable|date', 'end_date' => 'nullable|date'];
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $rules['end_date'] .= '|after_or_equal:start_date';
        }

        $validator = Validator::make($request->all(), $rules, [
            'start_date.date'         => 'Format tanggal awal tidak valid.',
            'end_date.date'           => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        if ($validator->fails()) {
            return view('owner.reports.weekly', [
                'reportData' => null,
                'startDate'  => $request->input('start_date'),
                'endDate'    => $request->input('end_date'),
            ])->withErrors($validator);
        }
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