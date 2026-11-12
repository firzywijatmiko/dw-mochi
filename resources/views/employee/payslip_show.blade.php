@extends('layouts.employee')

@section('content')
<div class="max-w-md mx-auto p-6 bg-white rounded-xl shadow">
    <h1 class="text-lg font-bold mb-1">Slip Gaji DW Mochi</h1>
    <p class="text-sm text-gray-600 mb-4">{{ $payslip->employee->name }}<br>
        Periode {{ $payslip->period_start->format('d M Y') }} - {{ $payslip->period_end->format('d M Y') }}</p>

    <table class="w-full text-sm mb-4">
        <tr><td>Hari hadir</td><td class="text-right">{{ $payslip->days_present }}</td></tr>
        <tr><td>Gaji pokok</td><td class="text-right">Rp{{ number_format($payslip->base_pay, 0, ',', '.') }}</td></tr>
        <tr><td>Uang makan</td><td class="text-right">Rp{{ number_format($payslip->meal_pay, 0, ',', '.') }}</td></tr>
        <tr><td>Lembur</td><td class="text-right">Rp{{ number_format($payslip->overtime_pay, 0, ',', '.') }}</td></tr>
        <tr><td>Uang sakit</td><td class="text-right">Rp{{ number_format($payslip->sick_pay, 0, ',', '.') }}</td></tr>
        <tr class="font-bold border-t"><td>Total</td><td class="text-right">Rp{{ number_format($payslip->total_pay, 0, ',', '.') }}</td></tr>
    </table>
    <p class="text-xs text-gray-500">Dibayar {{ $payslip->paid_at->format('d M Y H:i') }}</p>
    <button onclick="window.print()" class="mt-4 px-4 py-2 bg-[#0a192f] text-white rounded-lg text-sm">Cetak</button>
</div>
@endsection