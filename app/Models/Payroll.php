<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employees_id', 'period_start', 'period_end', 'days_present',
        'base_pay', 'meal_pay', 'overtime_pay', 'total_pay', 'status', 'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
        'base_pay'     => 'float',
        'meal_pay'     => 'float',
        'overtime_pay' => 'float',
        'total_pay'    => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }
}