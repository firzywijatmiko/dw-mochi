<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan DW Mochi</title>
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
    <h1>Laporan Keuangan DW Mochi</h1>
    <p class="sub">
        Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </p>

    <table class="summary">
        <tr><td>Total Pemasukan</td><td class="right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran Bahan Baku</td><td class="right">Rp {{ number_format($materialExpense, 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran Gaji Karyawan</td><td class="right">Rp {{ number_format($wageExpense, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pengeluaran</td><td class="right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td></tr>
        <tr class="profit">
            <td>Keuntungan Bersih</td>
            <td class="right" style="{{ $netProfit >= 0 ? 'color: #348b66;' : 'color: #bd4d48;' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <!-- RINCIAN HARIAN (MENGGUNAKAN $allDailyReport AGAR TERCETAK SEMUA DI PDF) -->
    <h1 style="margin-top:18px;font-size:13px;">Rincian Harian</h1>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pesanan</th>
                <th>Pendapatan</th>
                <th>Bahan Baku</th>
                <th>Gaji</th>
                <th>Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allDailyReport ?? [] as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td class="right">{{ $row['orders'] }}</td>
                <td class="right">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($row['material'], 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($row['wage'], 0, ',', '.') }}</td>
                <td class="right" style="{{ $row['profit'] >= 0 ? 'color: #348b66;' : 'color: #bd4d48;' }}">
                    Rp {{ number_format($row['profit'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #888;">Tidak ada transaksi harian pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h1 style="margin-top:18px;font-size:13px;">Rincian Pengeluaran</h1>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Item / Keterangan</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mergedExpenses as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->expense_date)->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong> <br>
                    <span style="color:#666; font-size: 9px;">{{ $item->notes ?? '-' }}</span>
                </td>
                <td class="right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #888;">Tidak ada catatan pengeluaran pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} oleh Sistem Manajemen DW Mochi</p>
</body>
</html>