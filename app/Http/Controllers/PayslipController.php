<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index()
    {
        return redirect()->route('employee.salary_history');
    }

    public function show(Payslip $payslip)
    {
        // Karyawan hanya boleh membuka slip miliknya sendiri
        abort_unless($payslip->employees_id === Auth::user()->employee->id, 403);

        if (!$payslip->isPrintable()) {
            return redirect()->route('employee.salary_history')->withErrors([
                'payslip' => 'Slip sudah melewati batas 1 bulan sejak tanggal dibayar dan tidak dapat dicetak.',
            ]);
        }

        return view('employee.payslip_show', compact('payslip'));
    }
}