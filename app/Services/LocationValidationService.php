<?php

namespace App\Services;

class LocationValidationService
{
    // Koordinat pusat DW Mochi (Ganti dengan koordinat asli lokasi produksi)
    protected $officeLat = -7.772540348956686; 
    protected $officeLng = 110.35613807281246;
    protected $maxRadius = 50; // Batas toleransi maksimal 50 meter

    public function isWithinRadius($lat, $lng)
    {
        $distance = $this->calculateHaversineDistance($lat, $lng, $this->officeLat, $this->officeLng);
        return $distance <= $this->maxRadius;
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        
        return $earthRadius * $c; // Jarak dalam meter
    }
}