<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Akun Owner
        User::create([
            'name' => 'Ibu Dwi',
            'phone' => '081234567890',
            'password' => Hash::make('mochi123'), // Sesuai info demo di prototype desain
            'role' => 'Owner',
            'status' => 'Aktif',
        ]);

        // Akun Karyawan
        User::create([
            'name' => 'Widyawati',
            'phone' => '089876543210',
            'password' => Hash::make('karyawan123'),
            'role' => 'Karyawan',
            'status' => 'Aktif',
        ]);
    }
}