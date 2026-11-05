<?php

namespace App\Services;

class LocationValidationService
{
    // Koordinat pusat (Ganti dengan koordinat kos kamu atau titik lokasi uji coba)
    protected $officeLat = -7.8061831; 
    protected $officeLng = 110.3180750;
    protected $maxRadius = 96; 

    public function isWithinRadius($lat, $lng)
    {

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