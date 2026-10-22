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

    /**
     * Label periode ala prototype, misal "4-10 Mei 2026" atau "30 Jun-6 Jul 2026".
     */
    public function getPeriodLabelAttribute()
    {
        $s = $this->period_start->copy()->locale('id');
        $e = $this->period_end->copy()->locale('id');

        if ($s->month === $e->month && $s->year === $e->year) {
            return $s->day . '-' . $e->day . ' ' . $e->translatedFormat('M Y');
        }

        return $s->day . ' ' . $s->translatedFormat('M') . '-' . $e->day . ' ' . $e->translatedFormat('M Y');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status === 'Dibayar' ? 'Sudah Dibayar' : 'Belum Dibayar';
    }
}