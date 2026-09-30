<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function checkDuplicate($phone, $pickupAt, $ignoreOrderId = null)
    {
        $query = Order::where('customer_phone', $phone)
                      ->whereDate('pickup_at', date('Y-m-d', strtotime($pickupAt)));
        
        if ($ignoreOrderId) {
            $query->where('id', '!=', $ignoreOrderId);
        }

        return $query->exists();
    }

    public function storeOrder($data, $variantsData, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $order = Order::create($data);

            foreach ($variantsData as $variant) {
                $order->details()->create([
                    'product_variant_id' => $variant['id'],
                    'quantity' => $variant['quantity'],
                    'unit_price' => $variant['price'],
                ]);
            }
            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}