<?php

namespace App\Service;

class ETAService
{
    private const RAYON_TERRE = 6371; // Rayon moyen de la Terre en km
    private const VITESSE_MOYENNE = 70; // Vitesse moyenne en km/h

    public function distanceHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::RAYON_TERRE * $c; // Distance en km
    }

    public function calculerETA(float $lat1, float $lon1, float $lat2, float $lon2): string
    {
        $distance = $this->distanceHaversine($lat1, $lon1, $lat2, $lon2);
        $temps = $distance / self::VITESSE_MOYENNE; // Temps en heures

        $heures = floor($temps);
        $minutes = round(($temps - $heures) * 60);

        return "{$heures}h {$minutes}min";
    }
}
