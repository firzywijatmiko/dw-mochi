<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Operasional DW Mochi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin: 0; color: #0f2137; }
        .sub { color: #666; margin: 2px 0 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .right { text-align: right; }
        .summary td:first-child { width: 55%; }
        .profit { font-weight: bold; }
        .footer { margin-top: 14px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <h1>Laporan Operasional DW Mochi</h1>
    <p class="sub">
        Periode {{ \Carbon\Carbon::parse($reportData['week_start'])->format('d/m/Y') }}
        s/d {{ \Carbon\Carbon::parse($reportData['week_end'])->format('d/m/Y') }}
    </p>

    <table class="summary">
        <tr><td>Pesanan Selesai</td><td class="right">{{ $reportData['total_orders'] }}</td></tr>
        <tr><td>Total Pendapatan</td><td class="right">Rp {{ number_format($reportData['total_revenue'], 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran Bahan Baku</td><td class="right">Rp {{ number_format($reportData['total_material_expense'], 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran Gaji Karyawan</td><td class="right">Rp {{ number_format($reportData['total_wage_expense'], 0, ',', '.') }}</td></tr>
        <tr><td>Total Pengeluaran</td><td class="right">Rp {{ number_format($reportData['total_expense'], 0, ',', '.') }}</td></tr>
        <tr class="profit"><td>Laba Bersih</td><td class="right">Rp {{ number_format($reportData['net_profit'], 0, ',', '.') }}</td></tr>
    </table>

    <h1 style="margin-top:18px;font-size:13px;">Rincian Harian</h1>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th><th>Pesanan</th><th>Pendapatan</th><th>Bahan Baku</th><th>Gaji</th><th>Laba</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['daily'] as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td class="right">{{ $row['orders'] }}</td>
                <td class="right">{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($row['material_expense'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($row['wage_expense'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($row['profit'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Dibuat pada {{ $reportData['generated_at']->format('d/m/Y H:i') }}</p>
</body>
</html>