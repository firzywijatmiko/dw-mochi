<?php

namespace App\Services;

use App\Models\RawMaterialExpense;

class ExpenseService
{
    /**
     * Cek duplikasi data pengeluaran berdasarkan nama item dan tanggal
     */
    public function checkDuplicate($itemName, $expenseDate, $ignoreExpenseId = null)
    {
        $query = RawMaterialExpense::where('item_name', $itemName)
                                   ->whereDate('expense_date', $expenseDate);
        
        if ($ignoreExpenseId) {
            $query->where('id', '!=', $ignoreExpenseId);
        }

        return $query->exists();
    }

    public function storeExpense(array $data, $userId)
    {
        $data['created_by'] = $userId;
        return RawMaterialExpense::create($data);
    }

    public function updateExpense(RawMaterialExpense $expense, array $data)
    {
        $expense->update($data);
        return $expense;
    }
}