<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LocationController extends Controller
{
    /**
     * POST /api/location/update
     * Android akan menembak ini setiap X menit di background.
     */
    public function update(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();

        // Update data lokasi terakhir dia
        $user->update([
            'last_latitude'  => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_seen_at'   => Carbon::now(),
        ]);

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => 'Lokasi diperbarui'
        ]);
    }
}
