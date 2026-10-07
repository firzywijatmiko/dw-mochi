<?php

// Aturan penggajian DW Mochi (sumber: laporan capstone, subbab 2.1.2)
return [
    // Jam kerja reguler 08.00 - 15.00 = 7 jam
    'work_start'         => '08:00',
    'work_end'           => '15:00',
    'regular_hours'      => 7,

    // Lembur tier 1 (kolom overtime_1x): 16.00-18.00, upah 2x tarif per jam
    'overtime_1x' => ['start' => '16:00', 'end' => '18:00', 'multiplier' => 2],

    // Lembur tier 2 (kolom overtime_2x): 18.00-21.00, upah 3x tarif per jam
    'overtime_2x' => ['start' => '18:00', 'end' => '21:00', 'multiplier' => 3],

    // Uang makan per hari hadir. ISI sesuai nominal dari Ibu Dwi.
    'meal_allowance'     => 0,

    // Periode gaji mingguan: Senin - Sabtu (dibayar hari Sabtu)
    'week_start_day'     => 1,
];