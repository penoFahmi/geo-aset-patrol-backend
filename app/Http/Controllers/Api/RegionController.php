<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'file_geojson' => 'required|file|mimes:json,geojson'
    ]);

    $jsonContent = file_get_contents($request->file('file_geojson')->getRealPath());

    Region::create([
        'name' => $request->name,
        'type' => 'kota',
        'geojson_data' => json_decode($jsonContent),
        'stroke_color' => '#FF0000'
    ]);

    return response()->json(['message' => 'Batas wilayah berhasil disimpan']);
}
}
