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
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:users,username',
            'phone'            => 'required|string|unique:users,phone',
            'password'         => 'required|string|min:6', 
            'base_daily_wage'  => 'required|numeric',
            'overtime_rate_1x' => 'nullable|numeric',
            'overtime_rate_2x' => 'nullable|numeric', // <-- TAMBAHKAN INI
            'status'           => 'required|in:Aktif,Nonaktif',
        ]);

        $this->employeeService->storeEmployee($validated);
        return redirect()->route('owner.employees.index')->with('success', 'Karyawan Berhasil Ditambahkan');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:users,username,' . $employee->users_id,
            'password'         => 'nullable|string|min:6', // Ubah jadi nullable agar tidak wajib isi saat update jika tidak mau ganti password
            'phone'            => 'required|string|unique:users,phone,' . $employee->users_id, 
            'base_daily_wage'  => 'required|numeric',
            'overtime_rate_1x' => 'nullable|numeric',
            'overtime_rate_2x' => 'nullable|numeric', // <-- TAMBAHKAN INI
            'status'           => 'required|in:Aktif,Nonaktif',
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