<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function index()
    {
        // Ambil data pengeluaran dari database
        $expenses = DB::table('raw_material_expenses')->orderBy('expense_date', 'desc')->get();
        
        return view('financial.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        // Simpan data pengeluaran baru ke database
        DB::table('raw_material_expenses')->insert([
            'created_by' => 1, // Menggunakan user ID 1 yang sudah dibuat di phpMyAdmin
            'item_name' => $request->item_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/keuangan')->with('success', 'Catatan pengeluaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        // Update data pengeluaran
        DB::table('raw_material_expenses')->where('id', $id)->update([
            'item_name' => $request->item_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
            'updated_at' => now(),
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