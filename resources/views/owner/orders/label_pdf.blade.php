<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Pesanan - P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            font-size: 11px;
        }
        /* Layout memanjang (Landscape) dengan border hitam putih tegas */
        .label-box {
            width: 100%;
            max-width: 380px;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 12px 15px;
            background: #fff;
            box-sizing: border-box;
        }
        /* Bagian Atas: Nama Brand & No Pesanan */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .brand {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .order-id {
            font-size: 11px;
            font-weight: bold;
        }
        /* Bagian Utama: Nama Pelanggan */
        .customer-section {
            text-align: center;
            margin: 10px 0;
        }
        .customer-title {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            margin-bottom: 2px;
        }
        .name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .phone {
            font-size: 10px;
            color: #333;
            margin-top: 2px;
        }
        /* Garis Pemisah */
        .divider {
            border-top: 1px solid #000;
            margin: 8px 0;
        }
        /* Bagian Rincian Produk */
        .items-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 3px;
        }
        .item-name {
            font-weight: 500;
        }
        .item-qty {
            font-weight: bold;
        }
        /* Bagian Bawah: Waktu Ambil & Footer Kotak-Kotak */
        .footer-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 8px;
            text-align: center;
        }
        .footer-col {
            display: table-cell;
            vertical-align: middle;
            font-size: 9px;
            padding: 0 4px;
            border-right: 1px solid #000;
        }
        .footer-col:last-child {
            border-right: none;
        }
        .footer-col strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
        }
        .notes-box {
            margin-top: 6px;
            font-size: 9px;
            background: #f4f4f4;
            padding: 4px 6px;
            border-radius: 4px;
            border-left: 2px solid #000;
        }
    </style>
</head>
<body>

<div class="label-box">
    <!-- Header: Brand & No Pesanan -->
    <div class="header-section">
        <div class="brand">DW Mochi</div>
        <div class="order-id">P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</div>
    </div>

    <!-- Nama Pelanggan -->
    <div class="customer-section">
        <div class="customer-title">Penerima Pesanan</div>
        <div class="name">{{ $order->customer_name }}</div>
        <div class="phone">{{ $order->customer_phone }}</div>
    </div>

    <div class="divider"></div>

    <!-- Daftar Produk yang Dipesan -->
    <div class="items-title">Detail Pesanan:</div>
    @foreach($order->details as $detail)
        <div class="item-row">
            <span class="item-name">• {{ optional($detail->productVariant)->name }}</span>
            <span class="item-qty">{{ $detail->quantity }} pcs</span>
        </div>
    @endforeach

    <!-- Catatan jika ada -->
    @if($order->notes)
        <div class="notes-box">
            <strong>Catatan:</strong> {{ $order->notes }}
        </div>
    @endif

    <!-- Footer: Informasi Waktu Ambil & Status -->
    <div class="footer-grid">
        <div class="footer-col" style="width: 60%;">
            <span style="font-size: 8px; color: #555;">WAKTU AMBIL</span>
            <strong>{{ \Carbon\Carbon::parse($order->pickup_at)->format('d M Y - H:i') }}</strong>
        </div>
        <div class="footer-col" style="width: 40%;">
            <span style="font-size: 8px; color: #555;">STATUS</span>
            <strong style="text-transform: uppercase;">{{ $order->status }}</strong>
        </div>
    </div>
</div>

</body>
</html>