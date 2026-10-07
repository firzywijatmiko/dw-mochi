<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employees_id', 'date', 'check_in_at', 'check_out_at',
        'check_in_lat', 'check_in_lng', 'check_out_lat', 'check_out_lng',
        'regular_hours', 'overtime_1x', 'overtime_2x', 'daily_wage', 'notes'
    ];

    protected $casts = [
        'date'          => 'date',
        'check_in_at'   => 'datetime',
        'check_out_at'  => 'datetime',
        'regular_hours' => 'float',
        'overtime_1x'   => 'float',
        'overtime_2x'   => 'float',
        'daily_wage'    => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }
}