<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;text-align:center;color:#0f2137;font-size:12px}.label{border:1px dashed #c28455;padding:18px 10px;margin:8px}.brand{font-size:10px;color:#c28455}.name{font-size:18px;font-weight:bold;margin:12px 0}.line{margin:6px 0;color:#555}</style></head>
<body><div class="label"><div class="brand">DW Mochi</div><div class="name">{{ $order->customer_name }}</div><div class="line">{{ $order->customer_phone }}</div><div class="line">P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</div>@foreach($order->details as $detail)<div class="line">{{ $detail->quantity }} pcs - {{ optional($detail->productVariant)->name }}</div><div class="line">Rp {{ number_format($detail->quantity * $detail->unit_price, 0, ',', '.') }}</div>@endforeach<div class="line">Ambil: {{ \Carbon\Carbon::parse($order->pickup_at)->format('d-m-Y H:i') }}</div></div></body>
</html>
