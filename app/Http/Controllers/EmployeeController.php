<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        $employees = Employee::with('user')->get();
        return view('owner.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        // Validasi Kelengkapan Data dan Cek Duplikasi Nomor HP[cite: 16]
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'base_daily_wage' => 'required|numeric',
            'overtime_rate_1x' => 'nullable|numeric',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $this->employeeService->storeEmployee($validated);

        return redirect()->route('owner.employees.index')->with('success', 'Karyawan Berhasil Ditambahkan');
    }

    public function update(Request $request, Employee $employee)
    {
        // Validasi Perubahan Data dan Cek Duplikasi (abaikan nomor HP milik sendiri)[cite: 17]
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $employee->users_id,
            'base_daily_wage' => 'required|numeric',
            'overtime_rate_1x' => 'nullable|numeric',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $this->employeeService->updateEmployee($employee, $validated);

        return redirect()->route('owner.employees.index')->with('success', 'Karyawan Berhasil Diubah');
    }

    public function destroy(Employee $employee)
    {
        // Menghapus data karyawan (Otomatis menghapus akun User karena cascade)
        $employee->user()->delete(); 
        
        return redirect()->route('owner.employees.index')->with('success', 'Data Karyawan Berhasil Dihapus');
    }
}