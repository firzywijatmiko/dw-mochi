<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Akun Owner
        User::updateOrCreate(
            ['username' => 'ownerDWMochi'],
            [
            'name'     => 'Dwi Wulandari',
            'phone'    => '081234567890',
            'password' => Hash::make('mochi123'),
            'role'     => 'Owner',
            'status'   => 'Aktif',
            ]
        );

        // Akun Karyawan
        $employeeUser = User::updateOrCreate(
            ['username' => 'widyawati'],
            [
            'name'     => 'Widyawati',
            'phone'    => '089876543210',
            'password' => Hash::make('karyawan123'),
            'role'     => 'Karyawan',
            'status'   => 'Aktif',
            ]
        );

        Employee::updateOrCreate(
            ['users_id' => $employeeUser->id],
            [
                'name' => 'Widyawati',
                'base_daily_wage' => 80000,
                'overtime_rate_1x' => 20000,
                'overtime_rate_2x' => 30000,
                'status' => 'Aktif',
            ]
        );
    }
}