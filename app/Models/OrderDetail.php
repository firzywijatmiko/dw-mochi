<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'product_variant_id', 'quantity', 'unit_price'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}