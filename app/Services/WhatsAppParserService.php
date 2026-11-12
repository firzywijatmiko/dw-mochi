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
            'is_valid' => true,
            'errors' => []
        ];

        // 1. Ekstrak Nama
        if (preg_match('/^Nama\s*:\s*(.+)$/im', $text, $matches)) {
            $name = trim($matches[1]);
            if ($name === '' || str_starts_with(strtolower($name), 'no') || str_starts_with(strtolower($name), 'tanggal')) {
                $parsedData['is_valid'] = false;
                $parsedData['errors'][] = 'Nama Pelanggan tidak boleh kosong';
            } else {
                $parsedData['customer_name'] = $name;
            }
        } else {
            $parsedData['is_valid'] = false;
            $parsedData['errors'][] = 'Format Nama Pelanggan tidak ditemukan';
        }

        // 2. Ekstrak No HP
        if (preg_match('/^No\.?\s*HP\s*:\s*((?:08|62|\+62)[\d\-\s]{8,13})$/im', $text, $matches) && trim($matches[1]) !== '') {
            $rawPhone = preg_replace('/[^0-9]/', '', $matches[1]);
            if (str_starts_with($rawPhone, '62')) {
                $rawPhone = '0' . substr($rawPhone, 2);
            }
            $parsedData['customer_phone'] = $rawPhone;
        } else {
            $parsedData['is_valid'] = false;
            $parsedData['errors'][] = 'No. HP kosong atau format salah (harus diawali 08)';
        }

        // 3. Ekstrak Tanggal & Waktu (Lebih Fleksibel)
        if (preg_match('/^Tanggal\s*(?:&|dan)\s*Waktu\s*Ambil\s*:\s*(.+)$/im', $text, $matches) && trim($matches[1]) !== '') {
            $parsedData['pickup_at'] = $this->parseFuzzyDate($matches[1]);
            if (empty($parsedData['pickup_at'])) {
                $parsedData['is_valid'] = false;
                $parsedData['errors'][] = 'Format Tanggal & Waktu Ambil tidak valid';
            }
        } else {
            $parsedData['is_valid'] = false;
            $parsedData['errors'][] = 'Tanggal & Waktu Ambil kosong';
        }

        // 4. Ekstrak Pesanan
        if (preg_match('/^Pesanan\s*:\s*\n(.*?)(?:^Catatan\s*:|\z)/ims', $text, $matches)) {
            $orderBlock = $matches[1];
            $activeVariants = ProductVariant::where('is_active', true)->get();
            
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
                $parsedData['is_valid'] = false;
                $parsedData['errors'][] = 'Daftar Varian Pesanan tidak valid atau format salah';
            }
        } else {
            $parsedData['is_valid'] = false;
            $parsedData['errors'][] = 'Bagian "Pesanan:" tidak ditemukan';
        }

        if (empty($parsedData['variants'])) {
            $parsedData['is_valid'] = false;
            if(!in_array('Varian rasa tidak dikenali sistem', $parsedData['errors'])) {
                $parsedData['errors'][] = 'Varian rasa tidak dikenali sistem';
            }
            $parsedData['variants'][] = ['id' => '', 'quantity' => 1, 'price' => 0];
        }

        // 5. Ekstrak Catatan
        if (preg_match('/^Catatan\s*:\s*(.*)$/im', $text, $matches)) {
            $parsedData['notes'] = trim($matches[1]);
        }

        return $parsedData;
    }

    private function parseFuzzyDate($rawDate)
    {
        $text = strtolower(trim($rawDate));
        $text = preg_replace('/(ya|kaa|kak|sis|dong|nih).*$/i', '', $text);
        
        $hour = 0;
        $minute = 0;
        
        // Tangkap berbagai variasi format jam (misal: "jam 10", "10.00", "14:30", "10 pagi", "3 sore")
        if (preg_match('/(?:jam|pukul)?\s*([0-9]{1,2})(?:[\.:]([0-9]{2}))?\s*(pagi|siang|sore|malam|malem|am|pm)?/i', $text, $timeMatches)) {
            $hour = (int) $timeMatches[1];
            $minute = isset($timeMatches[2]) ? (int) $timeMatches[2] : 0;
            $modifier = isset($timeMatches[3]) ? strtolower($timeMatches[3]) : '';
            
            if (in_array($modifier, ['siang', 'sore', 'malam', 'malem', 'pm']) && $hour < 12) {
                $hour += 12;
            }
            if ($modifier === 'pagi' && $hour === 12) {
                $hour = 0;
            }
        }

        $replacements = [
            'januari' => 'january', 'pebruari' => 'february', 'februari' => 'february',
            'maret' => 'march', 'mei' => 'may', 'juni' => 'june', 'juli' => 'july',
            'agustus' => 'august', 'oktobeer' => 'october', 'oktober' => 'october',
            'nopember' => 'november', 'november' => 'november', 'desember' => 'december',
            'malem' => '', 'malam' => '', 'sore' => '', 'siang' => '', 'pagi' => '',
            'jam' => '', 'pukul' => '', 'wib' => '', 'wita' => '', 'wit' => ''
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        $text = preg_replace('/(?:pukul|jam)?\s*[0-9]{1,2}(?:[\.:][0-9]{2})?\s*(pagi|siang|sore|malam|malem|am|pm)?/i', '', $text);
        $text = str_replace(',', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));

        try {
            $date = Carbon::parse($text);
            if ($date->year === 1970) throw new \Exception("Invalid date");
            
            $date->setTime($hour, $minute);
            return $date->format('Y-m-d H:i');
        } catch (\Exception $e) {
            return ''; 
        }
    }
}