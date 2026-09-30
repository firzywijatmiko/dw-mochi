<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id', 'name', 'base_daily_wage', 'overtime_rate_1x', 'overtime_rate_2x', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employees_id');
    }
}