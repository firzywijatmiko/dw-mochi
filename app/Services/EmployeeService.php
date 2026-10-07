<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function storeEmployee(array $data)
    {
        DB::beginTransaction();
        try {
            // Buat akun login Karyawan (password default disamakan dengan nomor HP)
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['phone']), 
                'role' => 'Karyawan',
                'status' => $data['status'],
            ]);

            // Simpan detail gaji karyawan
            $employee = Employee::create([
                'users_id' => $user->id,
                'name' => $data['name'],
                'base_daily_wage' => $data['base_daily_wage'],
                'overtime_rate_1x' => $data['overtime_rate_1x'] ?? 0,
                'overtime_rate_2x' => $data['overtime_rate_2x'] ?? 0,
                'status' => $data['status'],
            ]);

            DB::commit();
            return $employee;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEmployee(Employee $employee, array $data)
    {
        DB::beginTransaction();
        try {
            // Update akun user
            $employee->user->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $data['phone'],
                'status' => $data['status'],
            ]);

            // Update detail gaji
            $employee->update([
                'name' => $data['name'],
                'base_daily_wage' => $data['base_daily_wage'],
                'overtime_rate_1x' => $data['overtime_rate_1x'] ?? 0,
                'overtime_rate_2x' => $data['overtime_rate_2x'] ?? 0,
                'status' => $data['status'],
            ]);

            DB::commit();
            return $employee;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}