<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        $payslips = Payslip::where('employees_id', $employee->id)
            ->orderByDesc('period_start')
            ->get();

        return view('employee.payslips', compact('payslips'));
    }

    public function show(Payslip $payslip)
    {
        // Karyawan hanya boleh membuka slip miliknya sendiri
        abort_unless($payslip->employees_id === Auth::user()->employee->id, 403);

        if (!$payslip->isPrintable()) {
            return redirect()->route('employee.payslips')->withErrors([
                'payslip' => 'Slip sudah melewati batas 1 bulan sejak tanggal dibayar dan tidak dapat dicetak.',
            ]);
        }

        return view('employee.payslip_show', compact('payslip'));
    }
}