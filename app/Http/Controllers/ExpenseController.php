<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialExpense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index()
    {
        $expenses = RawMaterialExpense::latest('expense_date')->get();
        return view('owner.expenses.index', compact('expenses'));
    }

    // --- ACTIVITY DIAGRAM: TAMBAH PENGELUARAN ---
    public function store(Request $request)
    {
        // Validasi Kelengkapan Data[cite: 19]
        $request->validate([
            'item_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Cek Duplikasi Data Pengeluaran[cite: 19]
        if ($this->expenseService->checkDuplicate($request->item_name, $request->expense_date)) {
            return back()->with('error', 'Data Pengeluaran Sudah Ada pada tanggal tersebut.')->withInput();
        }

        // Simpan Data Pengeluaran Baru[cite: 19]
        $this->expenseService->storeExpense($request->all(), Auth::id());

        return redirect()->route('owner.expenses.index')->with('success', 'Pengeluaran Berhasil Ditambahkan');
    }

    // --- ACTIVITY DIAGRAM: UBAH PENGELUARAN ---
    public function update(Request $request, RawMaterialExpense $expense)
    {
        // Validasi Perubahan Data[cite: 20]
        $request->validate([
            'item_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Cek Duplikasi Data Pengeluaran (abaikan data yang sedang diubah)[cite: 20]
        if ($this->expenseService->checkDuplicate($request->item_name, $request->expense_date, $expense->id)) {
            return back()->with('error', 'Data Pengeluaran Sudah Ada.')->withInput();
        }

        // Update Data Perubahan[cite: 20]
        $this->expenseService->updateExpense($expense, $request->all());

        return redirect()->route('owner.expenses.index')->with('success', 'Pengeluaran Berhasil Diubah');
    }

    // --- ACTIVITY DIAGRAM: HAPUS PENGELUARAN ---
    public function destroy(RawMaterialExpense $expense)
    {
        // Hapus Data Pengeluaran Dari Database[cite: 21]
        $expense->delete();
        
        return redirect()->route('owner.expenses.index')->with('success', 'Data Pengeluaran Berhasil Dihapus');
    }
}