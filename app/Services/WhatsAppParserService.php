<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ProductVariant;

class WhatsAppParserService
{
    public function parse($rawTemplate)
    {
        $text = $rawTemplate;
        $lowerText = strtolower($text);

        $parsedData = [
            'customer_name' => '',
            'customer_phone' => '',
            'pickup_at' => '',
            'notes' => '',
            'variants' => [],
            'is_valid' => true
        ];

        // 1. Ekstrak No HP (Cari angka setelah variasi kata hp/wa tanpa mempedulikan awal baris)
        if (preg_match('/(?:no\s*hp|hp|wa|whatsapp|telepon|telp)[\s:=\-]*((?:08|62|\+62)[\d\-\s]{8,13})/i', $text, $matches)) {
            $rawPhone = preg_replace('/[^0-9]/', '', $matches[1]);
            if (str_starts_with($rawPhone, '62')) {
                $rawPhone = '0' . substr($rawPhone, 2);
            }
            $parsedData['customer_phone'] = $rawPhone;
        }

        // 2. Ekstrak Waktu Ambil
        if (preg_match('/(?:tgl|tanggal|waktu|jam|ambil)\s*[:=\-,\.]?\s*(\d{1,2}\s+[a-zA-Z]+\s+\d{4}.*?(?:pagi|siang|sore|malam|malem|am|pm|\b))/i', $text, $matches)) {
            $parsedData['pickup_at'] = $this->parseFuzzyDate($matches[1]);
        }

        // 3. Ekstrak Nama Pelanggan 
        if (preg_match('/(?:nama|atas nama|pemesan|name)\s*[:=\-]?\s*([a-zA-Z\s]+?)(?:no\s*hp|hp|wa|,|\n)/i', $text, $matches)) {
            $parsedData['customer_name'] = trim($matches[1]);
        } else if (preg_match('/^([a-zA-Z\s]+?)(?:no\s*hp|hp|wa|tgl|ambil|,|\n)/i', $text, $matches)) {
            // Tangkap kata di paling awal kalimat jika user langsung mengetik nama (misal: "Basna no hp...")
            $parsedData['customer_name'] = trim($matches[1]);
        }

        // 4. Ekstrak Varian (Cari nama rasa dan angka terdekatnya di seluruh teks)
        $activeVariants = ProductVariant::where('is_active', true)->get();
        $orderText = $text; 
        
        // Persempit pencarian angka hanya pada kalimat setelah kata order/pesan jika ada
        if (preg_match('/(?:order|pesan|pesanan|mau)[\s:=\-]*(.*)/is', $text, $matches)) {
            $orderText = $matches[1];
        }

        foreach ($activeVariants as $variant) {
            $vName = strtolower($variant->name);
            $keywords = explode(' ', str_replace('mochi ', '', $vName));
            
            foreach ($keywords as $kw) {
                if (trim($kw) === '') continue;
                
                // Cari nama rasa, kemudian tangkap angka yang muncul setelah nama tersebut
                $pattern = '/' . preg_quote($kw, '/') . '[^\d]*?(\d+)/i';
                if (preg_match($pattern, $orderText, $matches)) {
                    $parsedData['variants'][] = [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'quantity' => (int) $matches[1],
                        'price' => $variant->price
                    ];
                    break;
                } else if (str_contains(strtolower($orderText), $kw)) {
                    // Ketemu nama varian tapi tidak ada angka, asumsikan 1
                    $parsedData['variants'][] = [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'quantity' => 1,
                        'price' => $variant->price
                    ];
                    break;
                }
            }
        }

        if (empty($parsedData['variants'])) {
            $parsedData['variants'][] = ['id' => '', 'quantity' => 1, 'price' => 0];
        }

        // 5. Ekstrak Catatan Tambahan
        if (preg_match('/(?:catatan|note|keterangan)[\s:=\-]*(.*)$/is', $text, $matches)) {
            $parsedData['notes'] = trim($matches[1]);
        }

        return $parsedData;
    }

    private function parseFuzzyDate($rawDate)
    {
        $text = strtolower(trim($rawDate));

        // Bersihkan kata obrolan yang ikut tertangkap agar tidak merusak format tanggal
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
        
        // Ubah jam acak seperti "12 am" menjadi "12:00 am"
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