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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }
}