<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by', 'item_name', 'amount', 'expense_date', 'notes'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}