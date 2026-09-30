<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name', 'customer_phone', 'pickup_at', 'status', 'notes', 'raw_template', 'created_by'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}