<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Semua Label Selesai</title>
    <style>
        body { 
            font-family: Helvetica, Arial, sans-serif; 
            color: #000; 
            margin: 0; 
            padding: 5px; 
            font-size: 10px; 
        }
        /* Menggunakan struktur tabel murni agar aman di DomPDF */
        .label-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px; /* Jarak antar kotak label */
        }
        .label-cell {
            width: 50%; /* 2 kolom per baris */
            vertical-align: top;
        }
        .label-box {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 10px;
            background: #fff;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .header {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .customer {
            text-align: center;
            margin: 6px 0;
        }
        .name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .item-row {
            width: 100%;
            margin-bottom: 2px;
            font-size: 9px;
        }
        .footer {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 4px;
            font-size: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

<table class="label-table">
    @foreach($orders as $index => $order)
        {{-- Buka baris baru setiap 2 kolom --}}
        @if($index % 2 == 0)
            <tr>
        @endif

        <td class="label-cell">
            <div class="label-box">
                <!-- Header -->
                <table class="header">
                    <tr>
                        <td><b>DW MOCHI</b></td>
                        <td style="text-align: right;"><b>P-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</b></td>
                    </tr>
                </table>

                <!-- Customer -->
                <div class="customer">
                    <div style="font-size: 7px; color: #555; text-transform: uppercase;">Penerima</div>
                    <div class="name">{{ $order->customer_name }}</div>
                    <div style="font-size: 9px;">{{ $order->customer_phone }}</div>
                </div>

                <hr style="border: 0.5px solid #000; margin: 4px 0;">

                <!-- Details -->
                <div style="font-weight: bold; font-size: 8px; margin-bottom: 3px;">DETAIL:</div>
                @foreach($order->details as $detail)
                    <table class="item-row">
                        <tr>
                            <td>• {{ optional($detail->productVariant)->name }}</td>
                            <td style="text-align: right;"><b>{{ $detail->quantity }} pcs</b></td>
                        </tr>
                    </table>
                @endforeach

                <!-- Footer -->
                <div class="footer">
                    Ambil: {{ \Carbon\Carbon::parse($order->pickup_at)->format('d/m/Y H:i') }} | Status: SELESAI
                </div>
            </div>
        </td>

        {{-- Tutup baris setiap 2 kolom atau di akhir perulangan --}}
        @if($index % 2 != 0 || $loop->last)
            </tr>
        @endif
    @endforeach
</table>

</body>
</html>