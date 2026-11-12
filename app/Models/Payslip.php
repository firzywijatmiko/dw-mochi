<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_id', 'employees_id', 'period_start', 'period_end', 'days_present',
        'base_pay', 'meal_pay', 'overtime_pay', 'sick_pay', 'total_pay', 'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
        'base_pay'     => 'float',
        'meal_pay'     => 'float',
        'overtime_pay' => 'float',
        'sick_pay'     => 'float',
        'total_pay'    => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    // Slip bisa dicetak karyawan sampai 1 bulan sejak tanggal dibayar
    public function isPrintable(): bool
    {
        return $this->paid_at->copy()->addMonth()->greaterThanOrEqualTo(now());
    }
}