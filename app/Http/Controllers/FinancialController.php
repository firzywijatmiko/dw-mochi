<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function index()
    {
        // Ambil data pengeluaran dari database
        $expenses = DB::table('raw_material_expenses')->orderBy('expense_date', 'desc')->get();

        // Pemasukan: pesanan selesai (quantity x unit_price dari order_details)
        $totalIncome = (float) DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

        // Pengeluaran: bahan baku + gaji karyawan (sama dengan halaman Laporan)
        $materialExpense = (float) DB::table('raw_material_expenses')->sum('amount');
        $wageExpense     = (float) DB::table('attendances')->sum('daily_wage');
        $totalExpense    = $materialExpense + $wageExpense;

        $netProfit = $totalIncome - $totalExpense;

        // Data grafik tren: pemasukan vs pengeluaran, 5 minggu terakhir (Senin-Minggu)
        $chartData = [];
        $firstWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(4);

        for ($i = 0; $i < 5; $i++) {
            $start = $firstWeek->copy()->addWeeks($i)->startOfDay();
            $end   = $start->copy()->addDays(6)->endOfDay();

            $income = (float) DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.updated_at', [$start, $end])
                ->sum(DB::raw('order_details.quantity * order_details.unit_price'));

            $material = (float) DB::table('raw_material_expenses')
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');

            $wage = (float) DB::table('attendances')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->sum('daily_wage');

            $chartData[] = [
                'label'   => $start->copy()->locale('id')->translatedFormat('j M'),
                'income'  => $income,
                'expense' => $material + $wage,
            ];
        }

        // Batas atas sumbu Y dibulatkan ke angka yang rapi
        $highest = max(array_merge([0], array_column($chartData, 'income'), array_column($chartData, 'expense')));
        if ($highest <= 0) {
            $chartMax = 100000;
        } else {
            $unit     = pow(10, floor(log10($highest)));
            $chartMax = ceil($highest / $unit) * $unit;
        }

        return view('financial.index', compact('expenses', 'totalIncome', 'totalExpense', 'netProfit', 'chartData', 'chartMax'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'    => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ], [
            'amount.min'      => 'Nominal tidak boleh bernilai negatif.',
            'amount.numeric'  => 'Nominal harus berupa angka.',
            'amount.required' => 'Nominal wajib diisi.',
        ]);

        // Simpan data pengeluaran baru ke database
        DB::table('raw_material_expenses')->insert([
            'created_by'   => Auth::id(), // user yang sedang login
            'item_name'    => $validated['item_name'],
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes'        => $validated['notes'] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect('/keuangan')->with('success', 'Catatan pengeluaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'item_name'    => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string',
        ], [
            'amount.min'      => 'Nominal tidak boleh bernilai negatif.',
            'amount.numeric'  => 'Nominal harus berupa angka.',
            'amount.required' => 'Nominal wajib diisi.',
        ]);

        // Update data pengeluaran
        DB::table('raw_material_expenses')->where('id', $id)->update([
            'item_name'    => $validated['item_name'],
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes'        => $validated['notes'] ?? null,
            'updated_at'   => now(),
        ]);

        return redirect('/keuangan')->with('success', 'Catatan pengeluaran berhasil diubah!');
    }

    public function destroy($id)
    {
        // Hapus data pengeluaran dari database
        DB::table('raw_material_expenses')->where('id', $id)->delete();

        return redirect('/keuangan')->with('success', 'Catatan pengeluaran berhasil dihapus!');
    }
}