<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'product_variant_id', 'quantity', 'unit_price'];

    /**
     * Relasi balik ke tabel orders
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke tabel product_variants
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}