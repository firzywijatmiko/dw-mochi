<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ProductVariant;

class WhatsAppParserService
{
    public function parse($rawTemplate)
    {
        $text = trim($rawTemplate);

        $parsedData = [
            'customer_name' => '',
            'customer_phone' => '',
            'pickup_at' => '',
            'notes' => '',
            'variants' => [],
            'is_valid' => true
        ];

        // 1. Ekstrak Nama (Wajib diawali baris "Nama: ")
        if (preg_match('/^Nama\s*:\s*(.+)$/im', $text, $matches)) {
            $parsedData['customer_name'] = trim($matches[1]);
        } else {
            $parsedData['is_valid'] = false;
        }

        // 2. Ekstrak No HP (Wajib diawali baris "No. HP: " atau "No HP: ")
        if (preg_match('/^No\.?\s*HP\s*:\s*((?:08|62|\+62)[\d\-\s]{8,13})$/im', $text, $matches)) {
            $rawPhone = preg_replace('/[^0-9]/', '', $matches[1]);
            if (str_starts_with($rawPhone, '62')) {
                $rawPhone = '0' . substr($rawPhone, 2);
            }
            $parsedData['customer_phone'] = $rawPhone;
        } else {
            $parsedData['is_valid'] = false;
        }

        // 3. Ekstrak Tanggal (Wajib diawali baris "Tanggal & Waktu Ambil: ")
        if (preg_match('/^Tanggal\s*(?:&|dan)\s*Waktu\s*Ambil\s*:\s*(.+)$/im', $text, $matches)) {
            $parsedData['pickup_at'] = $this->parseFuzzyDate($matches[1]);
        } else {
            $parsedData['is_valid'] = false;
        }

        // 4. Ekstrak Pesanan (Wajib ada baris "Pesanan:" lalu diikuti list item dengan format "- Nama Rasa: Jumlah")
        if (preg_match('/^Pesanan\s*:\s*\n(.*?)(?:^Catatan\s*:|\z)/ims', $text, $matches)) {
            $orderBlock = $matches[1];
            $activeVariants = ProductVariant::where('is_active', true)->get();
            
            // Tangkap list dengan format "- Coklat: 10"
            preg_match_all('/^-\s*(.+?)\s*:\s*(\d+)/im', $orderBlock, $orderLines, PREG_SET_ORDER);
            
            if (count($orderLines) > 0) {
                foreach ($orderLines as $line) {
                    $vNameInput = strtolower(trim($line[1]));
                    $qty = (int) $line[2];
                    
                    $matchedVariant = null;
                    foreach ($activeVariants as $variant) {
                        $dbName = strtolower($variant->name);
                        $dbKeywords = explode(' ', str_replace('mochi ', '', $dbName));
                        
                        foreach ($dbKeywords as $kw) {
                            if ($kw !== '' && str_contains($vNameInput, $kw)) {
                                $matchedVariant = $variant;
                                break;
                            }
                        }
                        if ($matchedVariant) break;
                    }
                    
                    if ($matchedVariant) {
                        $parsedData['variants'][] = [
                            'id' => $matchedVariant->id,
                            'name' => $matchedVariant->name,
                            'quantity' => $qty,
                            'price' => $matchedVariant->price
                        ];
                    }
                }
            } else {
                // Jika ada "Pesanan:" tapi format listnya salah (tidak pakai strip dan titik dua)
                $parsedData['is_valid'] = false; 
            }
        } else {
            // Jika kata "Pesanan:" tidak ada
            $parsedData['is_valid'] = false; 
        }

        if (empty($parsedData['variants'])) {
            $parsedData['is_valid'] = false;
            $parsedData['variants'][] = ['id' => '', 'quantity' => 1, 'price' => 0];
        }

        // 5. Ekstrak Catatan (Opsional, tapi akan ditangkap jika diawali "Catatan: ")
        if (preg_match('/^Catatan\s*:\s*(.*)$/im', $text, $matches)) {
            $parsedData['notes'] = trim($matches[1]);
        }

        return $parsedData;
    }

    private function parseFuzzyDate($rawDate)
    {
        $text = strtolower(trim($rawDate));

        $text = preg_replace('/(ya|kaa|kak|sis|dong|nih).*$/i', '', $text);
        
        $replacements = [
            'januari' => 'january', 'pebruari' => 'february', 'februari' => 'february',
            'maret' => 'march', 'mei' => 'may', 'juni' => 'june', 'juli' => 'july',
            'agustus' => 'august', 'oktobeer' => 'october', 'oktober' => 'october',
            'nopember' => 'november', 'november' => 'november', 'desember' => 'december',
            'malem' => 'PM', 'malam' => 'PM', 'sore' => 'PM', 'siang' => 'PM', 'pagi' => 'AM',
            'jam' => '', 'pukul' => '', 'wib' => '', 'wita' => '', 'wit' => ''
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        $text = str_replace(',', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        $text = preg_replace('/(\d{1,2})\s*(am|pm)/i', '$1:00 $2', $text);

        try {
            $date = Carbon::parse($text);
            if ($date->year === 1970) throw new \Exception("Invalid date");
            return $date->format('Y-m-d H:i');
        } catch (\Exception $e) {
            return ''; 
        }
    }
}