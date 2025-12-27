<?php

namespace App\Services;

class RouteOptimizerService
{
    /**
     * Titik Awal Patroli
     * Ganti koordinat ini sesuai lokasi kantor aslimu!
     */
    protected $startLat = -0.05020240175254746;
    protected $startLng = 109.33758587544017;

    /**
     * RUMUS HAVERSINE
     * Menghitung jarak lurus antara 2 titik bumi (dalam meter).
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Radius Bumi
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Hasil jarak dalam meter
    }

    /**
     * ALGORITMA NEAREST NEIGHBOR (Inti Skripsi)
     * Mengurutkan daftar aset dari yang terdekat.
     * * @param Collection $assets Data aset yang dipilih admin
     * @return array Data aset yang sudah urut
     */
    public function optimize($assets)
    {
        $sortedRoute = [];
        $remainingAssets = $assets->toArray();

        // Posisi "Sekarang"
        $currentLat = $this->startLat;
        $currentLng = $this->startLng;

        while (!empty($remainingAssets)) {
            $nearestIndex = -1;
            $shortestDistance = PHP_INT_MAX;

            // 1. Cari aset mana yang paling dekat dari posisi "Sekarang"
            foreach ($remainingAssets as $index => $asset) {
                if ($asset['centroid_lat'] == null || $asset['centroid_lng'] == null) continue;

                $dist = $this->calculateDistance(
                    $currentLat, $currentLng,
                    $asset['centroid_lat'], $asset['centroid_lng']
                );

                if ($dist < $shortestDistance) {
                    $shortestDistance = $dist;
                    $nearestIndex = $index;
                }
            }

            // 2. Jika ketemu aset terdekat...
            if ($nearestIndex != -1) {
                $nearestAsset = $remainingAssets[$nearestIndex];

                // Tambahkan info urutan
                $nearestAsset['sequence_order'] = count($sortedRoute) + 1;
                $nearestAsset['distance_from_prev'] = round($shortestDistance, 2); // Info jarak (opsional)

                // Masukkan ke array hasil
                $sortedRoute[] = $nearestAsset;

                // Update posisi "Sekarang" jadi di aset ini (lanjut perjalanan dari sini)
                $currentLat = $nearestAsset['centroid_lat'];
                $currentLng = $nearestAsset['centroid_lng'];

                // Hapus dari daftar sisa
                unset($remainingAssets[$nearestIndex]);
            } else {
                // Jaga-jaga kalau sisa aset error (gak punya koordinat), break loop biar gak hang
                break;
            }
        }

        return $sortedRoute;
    }
}
