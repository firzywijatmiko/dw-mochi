<?php

namespace App\Services;

class WhatsAppParserService
{
    /**
     * Mengekstrak teks WA berdasarkan format baku
     * Contoh Format WA:
     * Nama: Ibu Sari
     * HP: 08123456789
     * Waktu Ambil: 2026-07-01 14:00
     * Catatan: Tolong dibungkus rapi
     */
    public function parse($rawText)
    {
        $data = [
            'customer_name' => '',
            'customer_phone' => '',
            'pickup_at' => '',
            'notes' => '',
            'is_valid' => false
        ];

        // Ekstraksi menggunakan Regex (Sesuaikan dengan template WA DW Mochi)
        if (preg_match('/Nama:\s*(.*)/i', $rawText, $matches)) $data['customer_name'] = trim($matches[1]);
        if (preg_match('/HP:\s*(.*)/i', $rawText, $matches)) $data['customer_phone'] = trim($matches[1]);
        if (preg_match('/Waktu Ambil:\s*(.*)/i', $rawText, $matches)) $data['pickup_at'] = date('Y-m-d H:i:s', strtotime(trim($matches[1])));
        if (preg_match('/Catatan:\s*(.*)/i', $rawText, $matches)) $data['notes'] = trim($matches[1]);

        // Validasi format sesuai Activity Diagram Parsing WA
        if (!empty($data['customer_name']) && !empty($data['customer_phone'])) {
            $data['is_valid'] = true;
        }

        return $data;
    }
}