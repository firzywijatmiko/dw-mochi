<?php

namespace App\Services;

class LocationValidationService
{
    // Koordinat pusat (Ganti dengan koordinat kos kamu atau titik lokasi uji coba)
    protected $officeLat = -7.772540348956686; 
    protected $officeLng = 110.35613807281246;
    protected $maxRadius = 10000; // Diperbesar jadi 10 km selama masa testing di kos

    public function isWithinRadius($lat, $lng)
    {
        // TIP: Jika ingin 100% langsung tembus tanpa validasi GPS dulu selama testing, 
        // kamu bisa aktifkan baris ini: return true;

        $distance = $this->calculateHaversineDistance($lat, $lng, $this->officeLat, $this->officeLng);
        return $distance <= $this->maxRadius;
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1); // Diperbaiki operator pengurangannya
        $dLon = deg2rad($lon2 - $lon1); // Diperbaiki operator pengurangannya

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a)); // Diperbaiki operator perkaliannya
        
        return $earthRadius * $c; // Diperbaiki operator perkaliannya (Jarak dalam meter)
    }
}